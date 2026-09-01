<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SocialPublish;
use App\Services\Social\SocialAutoPostService;
use Illuminate\Http\Request;

/**
 * Admin → Social Auto-Post.
 *
 * One page: master switch, message template, per-network credentials
 * (secrets stored encrypted + masked), "Test connection" per network,
 * and the delivery log with retry buttons.
 */
class SocialController extends Controller
{
    public function __construct(private SocialAutoPostService $social)
    {
    }

    public function index()
    {
        $networks = SocialPublish::NETWORKS;
        $rows = SocialPublish::with('post')
            ->latest('updated_at')
            ->limit(50)
            ->get();

        return view('admin.social.index', [
            'social'    => $this->social,
            'networks'  => $networks,
            'rows'      => $rows,
            'enabled'   => $this->social->enabled(),
            'template'  => \App\Models\Setting::get(SocialAutoPostService::TEMPLATE_KEY, SocialAutoPostService::DEFAULT_TEMPLATE),
            'active'    => $this->social->activeNetworks(),
        ]);
    }

    public function update(Request $request)
    {
        $request->validate([
            'social_autopost_enabled' => ['nullable', 'in:1'],
            'social_message_template' => ['nullable', 'string', 'max:1000'],
            // plain (non-secret) fields
            'facebook_page_id'        => ['nullable', 'string', 'max:120'],
            'linkedin_author_urn'     => ['nullable', 'string', 'max:120'],
            'instagram_user_id'       => ['nullable', 'string', 'max:120'],
            'telegram_chat_id'        => ['nullable', 'string', 'max:120'],
            'pinterest_board_id'      => ['nullable', 'string', 'max:120'],
            // secrets (leave blank to keep the stored value)
            'x_consumer_key'          => ['nullable', 'string', 'max:300'],
            'x_consumer_secret'       => ['nullable', 'string', 'max:300'],
            'x_access_token'          => ['nullable', 'string', 'max:300'],
            'x_access_token_secret'   => ['nullable', 'string', 'max:300'],
            'facebook_page_token'     => ['nullable', 'string', 'max:600'],
            'linkedin_access_token'   => ['nullable', 'string', 'max:900'],
            'instagram_access_token'  => ['nullable', 'string', 'max:600'],
            'telegram_bot_token'      => ['nullable', 'string', 'max:300'],
            'pinterest_access_token'  => ['nullable', 'string', 'max:600'],
        ]);

        \App\Models\Setting::set(
            SocialAutoPostService::ENABLED_KEY,
            $request->boolean('social_autopost_enabled') ? '1' : '0',
            'text', 'social'
        );
        \App\Models\Setting::set(
            SocialAutoPostService::TEMPLATE_KEY,
            (string) $request->input('social_message_template', SocialAutoPostService::DEFAULT_TEMPLATE),
            'text', 'social'
        );

        foreach (['facebook_page_id', 'linkedin_author_urn', 'instagram_user_id', 'telegram_chat_id', 'pinterest_board_id'] as $field) {
            if ($request->has($field)) {
                \App\Models\Setting::set($field, trim((string) $request->input($field)), 'text', 'social');
            }
        }

        // Secrets: an empty field means "keep the existing one", a "remove_*"
        // checkbox clears it. Values are encrypted before they touch the DB.
        foreach (array_keys(SocialAutoPostService::SECRET_KEYS) as $secret) {
            if ($request->boolean('remove_'.$secret)) {
                \App\Models\Setting::set($secret, '', 'secret', 'social');
            } elseif (trim((string) $request->input($secret, '')) !== '') {
                $this->social->setCredential($secret, trim((string) $request->input($secret)));
            }
        }

        \App\Models\Setting::flushAllCache();

        return redirect()
            ->route('admin.social.index')
            ->with('success', 'Social Auto-Post settings saved.');
    }

    /** AJAX: verify one network's credentials without publishing anything. */
    public function test(Request $request)
    {
        $request->validate(['network' => ['required', 'in:'.implode(',', SocialPublish::NETWORKS)]]);
        $result = $this->social->testNetwork($request->input('network'));
        return response()->json($result);
    }

    /** Retry one logged publish row (admin action). */
    public function retry(Request $request, SocialPublish $publish)
    {
        $post = $publish->post;
        if (!$post || $post->status !== 'published' || $post->trashed()) {
            return back()->with('error', 'The post is no longer published — nothing to retry.');
        }
        $rows = $this->social->publish($post, [$publish->network], force: false);
        $row = $rows[0] ?? null;
        if ($row && $row->status === 'success') {
            return back()->with('success', ucfirst(SocialPublish::networkLabel($publish->network)).' post published.');
        }
        return back()->with('error', 'Retry failed: '.($row->error ?? 'unknown error'));
    }

    /**
     * Push a post to all configured networks right now (share screen button).
     * Runs inline so the admin sees the delivery result immediately.
     */
    public function pushNow(Request $request, \App\Models\Post $post)
    {
        if ($post->status !== 'published') {
            return back()->with('error', 'Only published posts can be pushed to social media.');
        }
        $targets = $this->social->activeNetworks();
        if (empty($targets)) {
            return back()->with('error', 'No network is fully configured yet — add credentials and enable at least one network above.');
        }
        $this->social->publish($post, $targets);
        return redirect()
            ->route('admin.posts.share', $post)
            ->with('success', 'Auto-post run finished — see the per-network status below.');
    }
}

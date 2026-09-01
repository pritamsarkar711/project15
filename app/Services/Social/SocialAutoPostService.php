<?php

namespace App\Services\Social;

use App\Models\Post;
use App\Models\SocialPublish;
use App\Models\Setting;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Huvanti Social Auto-Post — publish new posts to social media automatically.
 *
 * Supported networks (all optional, all admin-configured, no packages needed):
 *   x         — X (Twitter) via API v2 + OAuth 1.0a user context
 *   facebook  — Facebook Page feed via Graph API (page access token)
 *   linkedin  — LinkedIn UGC post (member or organization URN)
 *   instagram — Instagram Graph API (media container + publish; needs a
 *               featured image, because IG posts are image posts)
 *   telegram  — Telegram channel/group via bot API (link preview does the rest)
 *   pinterest — Pin creation via Pinterest API v5 (needs a featured image)
 *
 * SECURITY MODEL
 *   - Every token/secret is stored ENCRYPTED AT REST (Laravel Crypt, APP_KEY).
 *   - Values are decrypted only inside this class, only at call time.
 *   - They are NEVER echoed back to the browser: the admin UI shows a
 *     masked hint (•••• + last 4 chars) and "leave blank to keep existing".
 *   - Failure of one network never blocks the others or the post save.
 */
class SocialAutoPostService
{
    public const ENABLED_KEY = 'social_autopost_enabled';
    public const TEMPLATE_KEY = 'social_message_template';
    public const DEFAULT_TEMPLATE = "{title}\n\n{excerpt}\n\n{url}";

    /** secret-key => label used in the admin UI */
    public const SECRET_KEYS = [
        'x_consumer_key'         => 'X API Key (consumer key)',
        'x_consumer_secret'      => 'X API Key Secret',
        'x_access_token'         => 'X Access Token',
        'x_access_token_secret'  => 'X Access Token Secret',
        'facebook_page_token'    => 'Facebook Page Access Token',
        'linkedin_access_token'  => 'LinkedIn Access Token',
        'instagram_access_token' => 'Instagram Access Token',
        'telegram_bot_token'     => 'Telegram Bot Token',
        'pinterest_access_token' => 'Pinterest Access Token',
    ];

    /** -----------------------------------------------------------------
     *  Settings helpers (plain values)
     *  ----------------------------------------------------------------- */

    public function enabled(): bool
    {
        return Setting::get(self::ENABLED_KEY, '0') === '1';
    }

    public function isNetworkEnabled(string $network): bool
    {
        return Setting::get($network.'_enabled', '0') === '1';
    }

    /** Plain (non-secret) config fields required per network. */
    public static function requiredFields(string $network): array
    {
        return match ($network) {
            'x'         => ['x_consumer_key', 'x_consumer_secret', 'x_access_token', 'x_access_token_secret'],
            'facebook'  => ['facebook_page_id', 'facebook_page_token'],
            'linkedin'  => ['linkedin_author_urn', 'linkedin_access_token'],
            'instagram' => ['instagram_user_id', 'instagram_access_token'],
            'telegram'  => ['telegram_chat_id', 'telegram_bot_token'],
            'pinterest' => ['pinterest_board_id', 'pinterest_access_token'],
            default     => [],
        };
    }

    /** Networks the admin turned on AND fully configured. */
    public function activeNetworks(): array
    {
        $out = [];
        foreach (SocialPublish::NETWORKS as $network) {
            if (!$this->isNetworkEnabled($network)) continue;
            $ready = true;
            foreach (self::requiredFields($network) as $field) {
                if (in_array($field, array_keys(self::SECRET_KEYS), true)) {
                    if (!$this->isConfigured($field)) { $ready = false; break; }
                } elseif (trim((string) Setting::get($field, '')) === '') {
                    $ready = false; break;
                }
            }
            if ($ready) $out[] = $network;
        }
        return $out;
    }

    /** -----------------------------------------------------------------
     *  Credential storage — encrypted at rest, masked in the UI
     *  ----------------------------------------------------------------- */

    public function setCredential(string $key, string $plain): void
    {
        Setting::set($key, $plain === '' ? '' : Crypt::encryptString($plain), 'secret', 'social');
    }

    /** Decrypted value — ONLY for making API calls, never for display. */
    public function getCredential(string $key): string
    {
        $stored = (string) Setting::get($key, '');
        if ($stored === '') return '';
        // Crypt payloads are base64 starting "eyJ" ({"..."). Anything else is a
        // legacy plain value written before encryption existed — use as-is.
        if (!str_starts_with($stored, 'eyJ')) return $stored;
        try {
            return Crypt::decryptString($stored);
        } catch (\Throwable $e) {
            Log::warning('Social credential could not be decrypted', ['key' => $key]);
            return '';
        }
    }

    public function isConfigured(string $key): bool
    {
        return $this->getCredential($key) !== '';
    }

    /** UI-safe hint: never reveals the secret. */
    public function mask(string $key): string
    {
        $v = $this->getCredential($key);
        return $v === '' ? '' : 'configured — ends "…'.mb_substr($v, -4).'"';
    }

    /** -----------------------------------------------------------------
     *  Message building
     *  ----------------------------------------------------------------- */

    /** Strip tags + decode entities + collapse whitespace (WordPress-free). */
    public static function plainText(?string $html): string
    {
        $text = html_entity_decode(strip_tags((string) $html), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        return trim(preg_replace('/\s+/u', ' ', $text) ?? '');
    }

    public function buildMessage(Post $post, ?string $template = null): string
    {
        $tpl = $template ?? Setting::get(self::TEMPLATE_KEY, self::DEFAULT_TEMPLATE);
        if (trim($tpl) === '') $tpl = self::DEFAULT_TEMPLATE;
        $excerpt = self::plainText($post->excerpt ?? '') ?: Str::limit(self::plainText($post->content), 200);
        $msg = str_replace(
            ['{title}', '{excerpt}', '{url}', '{site}'],
            [$post->title, $excerpt, $post->publicUrl(), (string) config('app.name', 'Huvanti')],
            $tpl
        );
        return trim(preg_replace("/\n{3,}/", "\n\n", $msg) ?? $msg);
    }

    /** -----------------------------------------------------------------
     *  Publishing
     *  ----------------------------------------------------------------- */

    /**
     * Publish a post to every active network (once per network per post).
     * Returns the fresh SocialPublish rows for display in the share screen.
     */
    public function publish(Post $post, ?array $networks = null, bool $force = false): array
    {
        if (!$this->enabled()) return [];

        $rows = [];
        $targets = $networks ?? $this->activeNetworks();

        foreach ($targets as $network) {
            if (!in_array($network, SocialPublish::NETWORKS, true)) continue;

            $row = SocialPublish::firstOrNew(['post_id' => $post->id, 'network' => $network]);
            // One successful auto-post per network per post — never spam reposts.
            if ($row->exists && $row->status === 'success' && !$force) {
                $rows[] = $row;
                continue;
            }

            $row->post_id = $post->id;
            $row->network = $network;
            $row->attempts = $row->attempts + 1;

            try {
                $result = match ($network) {
                    'x'         => $this->postToX($post),
                    'facebook'  => $this->postToFacebook($post),
                    'linkedin'  => $this->postToLinkedIn($post),
                    'instagram' => $this->postToInstagram($post),
                    'telegram'  => $this->postToTelegram($post),
                    'pinterest' => $this->postToPinterest($post),
                    default     => ['ok' => false, 'error' => "Unknown network {$network}"],
                };
                if ($result['ok']) {
                    $row->status = 'success';
                    $row->external_url = $result['url'] ?? null;
                    $row->error = null;
                    $row->published_at = now();
                } else {
                    $row->status = 'failed';
                    $row->error = mb_substr($result['error'] ?? 'Unknown error', 0, 1000);
                }
            } catch (\Throwable $e) {
                $row->status = 'failed';
                $row->error = mb_substr($e->getMessage(), 0, 1000);
                Log::warning('Social auto-post failed', ['network' => $network, 'post' => $post->id, 'err' => $e->getMessage()]);
            }

            $row->save();
            $rows[] = $row;
        }

        return $rows;
    }

    /** -----------------------------------------------------------------
     *  Network clients (each returns ['ok'=>bool, 'url'|'error'=>...])
     *  ----------------------------------------------------------------- */

    private function postToX(Post $post): array
    {
        $ck  = $this->getCredential('x_consumer_key');
        $cs  = $this->getCredential('x_consumer_secret');
        $at  = $this->getCredential('x_access_token');
        $ats = $this->getCredential('x_access_token_secret');
        if (!$ck || !$cs || !$at || !$ats) return ['ok' => false, 'error' => 'X API credentials incomplete.'];

        // X counts every URL as 23 chars — keep the text within 280.
        $url = $post->publicUrl();
        $title = $post->title;
        $over = mb_strlen($title) + 1 + 23 - 280;
        if ($over > 0) $title = rtrim(mb_substr($title, 0, mb_strlen($title) - $over - 1)).'…';
        $text = $title.' '.$url;

        $headers = $this->oauth1Header('POST', 'https://api.twitter.com/2/tweets', [], $ck, $cs, $at, $ats);

        $response = Http::timeout(15)->connectTimeout(8)
            ->withHeaders(['Authorization' => $headers, 'Content-Type' => 'application/json'])
            ->post('https://api.twitter.com/2/tweets', ['text' => $text]);

        if ($response->successful()) {
            $id = $response->json('data.id');
            return ['ok' => true, 'url' => $id ? "https://x.com/i/web/status/{$id}" : null];
        }
        return ['ok' => false, 'error' => 'X API '.$response->status().': '.$response->body()];
    }

    /** Build the OAuth 1.0a Authorization header (HMAC-SHA1, no query params). */
    private function oauth1Header(string $method, string $url, array $extraOauthParams,
                                  string $consumerKey, string $consumerSecret,
                                  string $token, string $tokenSecret): string
    {
        $params = [
            'oauth_consumer_key'     => $consumerKey,
            'oauth_nonce'            => bin2hex(random_bytes(16)),
            'oauth_signature_method' => 'HMAC-SHA1',
            'oauth_timestamp'        => (string) time(),
            'oauth_token'            => $token,
            'oauth_version'          => '1.0',
        ] + $extraOauthParams;

        $enc = fn ($v) => str_replace('%7E', '~', rawurlencode((string) $v));
        uksort($params, 'strcmp');
        $pairs = [];
        foreach ($params as $k => $v) $pairs[] = $enc($k).'='.$enc($v);
        $baseString = strtoupper($method).'&'.$enc($url).'&'.$enc(implode('&', $pairs));
        $key = $enc($consumerSecret).'&'.$enc($tokenSecret);
        $params['oauth_signature'] = base64_encode(hash_hmac('sha1', $baseString, $key, true));

        uksort($params, 'strcmp');
        $parts = [];
        foreach ($params as $k => $v) $parts[] = $enc($k).'="'.$enc($v).'"';
        return 'OAuth '.implode(', ', $parts);
    }

    private function postToFacebook(Post $post): array
    {
        $pageId = trim((string) Setting::get('facebook_page_id', ''));
        $token  = $this->getCredential('facebook_page_token');
        if (!$pageId || !$token) return ['ok' => false, 'error' => 'Facebook Page ID / token incomplete.'];

        $response = Http::timeout(15)->connectTimeout(8)
            ->asJson()
            ->post("https://graph.facebook.com/v19.0/{$pageId}/feed", [
                'message' => $this->buildMessage($post),
                'link'    => $post->publicUrl(),
                'access_token' => $token,
            ]);
        if ($response->successful()) {
            $id = $response->json('id');
            return ['ok' => true, 'url' => $id ? 'https://www.facebook.com/'.$id : null];
        }
        return ['ok' => false, 'error' => 'Facebook Graph '.$response->status().': '.$response->body()];
    }

    private function postToLinkedIn(Post $post): array
    {
        $token = $this->getCredential('linkedin_access_token');
        $author = trim((string) Setting::get('linkedin_author_urn', '')); // urn:li:organization:123 or urn:li:person:ABC
        if (!$token || !$author) return ['ok' => false, 'error' => 'LinkedIn token / member URN incomplete.'];

        $payload = [
            'author'          => $author,
            'commentary'      => $this->buildMessage($post),
            'visibility'      => 'PUBLIC',
            'distribution'    => ['linkedInDistributionTarget' => ['targetedEntities' => []]],
            'lifecycleState'  => 'PUBLISHED',
            'isReshareDisabledByAuthor' => false,
        ];
        $response = Http::timeout(15)->connectTimeout(8)
            ->withHeaders([
                'Authorization'             => 'Bearer '.$token,
                'X-Restli-Protocol-Version' => '2.0.0',
                'Content-Type'              => 'application/json',
            ])
            ->post('https://api.linkedin.com/v2/ugcPosts', $payload);

        if ($response->successful()) {
            $id = $response->json('id');
            $slug = $id ? preg_replace('/^urn:li:(share|ugcPost):/', '', $id) : null;
            return ['ok' => true, 'url' => $slug ? "https://www.linkedin.com/feed/update/urn:li:ugcPost:{$slug}" : null];
        }
        return ['ok' => false, 'error' => 'LinkedIn API '.$response->status().': '.$response->body()];
    }

    private function postToInstagram(Post $post): array
    {
        $igUser = trim((string) Setting::get('instagram_user_id', ''));
        $token  = $this->getCredential('instagram_access_token');
        if (!$igUser || !$token) return ['ok' => false, 'error' => 'Instagram user ID / token incomplete.'];
        if (empty($post->featured_image)) {
            return ['ok' => false, 'error' => 'Instagram requires an image. Add a featured image to this post, then retry.'];
        }

        $imageUrl = $this->absoluteStorageUrl($post);

        // Step 1: media container (image must be publicly reachable — it is,
        // because the post is live and /storage streams publicly).
        $container = Http::timeout(20)->connectTimeout(8)->asJson()
            ->post("https://graph.facebook.com/v19.0/{$igUser}/media", [
                'image_url'    => $imageUrl,
                'caption'      => mb_substr($this->buildMessage($post), 0, 2200),
                'access_token' => $token,
            ]);
        if (!$container->successful()) {
            return ['ok' => false, 'error' => 'Instagram container '.$container->status().': '.$container->body()];
        }
        $creationId = $container->json('id');
        if (!$creationId) return ['ok' => false, 'error' => 'Instagram returned no container id.'];

        // Step 2: publish the container.
        $publish = Http::timeout(20)->connectTimeout(8)->asJson()
            ->post("https://graph.facebook.com/v19.0/{$igUser}/media_publish", [
                'creation_id'  => $creationId,
                'access_token' => $token,
            ]);
        if ($publish->successful()) {
            return ['ok' => true, 'url' => null];
        }
        return ['ok' => false, 'error' => 'Instagram publish '.$publish->status().': '.$publish->body()];
    }

    private function postToTelegram(Post $post): array
    {
        $token = $this->getCredential('telegram_bot_token');
        $chat  = trim((string) Setting::get('telegram_chat_id', ''));
        if (!$token || !$chat) return ['ok' => false, 'error' => 'Telegram bot token / chat ID incomplete.'];

        $text = '<b>'.e($post->title)."</b>\n\n".e(Str::limit(self::plainText($post->excerpt ?? $post->content), 220))."\n\n".$post->publicUrl();
        $response = Http::timeout(15)->connectTimeout(8)->asJson()
            ->post("https://api.telegram.org/bot{$token}/sendMessage", [
                'chat_id'                  => $chat,
                'text'                     => $text,
                'parse_mode'               => 'HTML',
                'disable_web_page_preview' => false,
            ]);
        if ($response->successful()) {
            return ['ok' => true, 'url' => null];
        }
        return ['ok' => false, 'error' => 'Telegram API '.$response->status().': '.$response->body()];
    }

    private function postToPinterest(Post $post): array
    {
        $token = $this->getCredential('pinterest_access_token');
        $board = trim((string) Setting::get('pinterest_board_id', ''));
        if (!$token || !$board) return ['ok' => false, 'error' => 'Pinterest token / board ID incomplete.'];
        if (empty($post->featured_image)) {
            return ['ok' => false, 'error' => 'Pinterest requires an image. Add a featured image, then retry.'];
        }

        $imageUrl = $this->absoluteStorageUrl($post);
        $response = Http::timeout(20)->connectTimeout(8)->asJson()
            ->withHeaders(['Authorization' => 'Bearer '.$token])
            ->post('https://api.pinterest.com/v5/pins', [
                'board_id'     => $board,
                'title'        => mb_substr($post->title, 0, 100),
                'description'  => mb_substr(self::plainText($post->excerpt ?? $post->content).' '.$post->publicUrl(), 0, 500),
                'link'         => $post->publicUrl(),
                'media_source' => ['source_type' => 'image_url', 'url' => $imageUrl],
            ]);
        if ($response->successful()) {
            $id = $response->json('id');
            return ['ok' => true, 'url' => $id ? "https://www.pinterest.com/pin/{$id}/" : null];
        }
        return ['ok' => false, 'error' => 'Pinterest API '.$response->status().': '.$response->body()];
    }

    private function absoluteStorageUrl(Post $post): string
    {
        if (str_starts_with((string) $post->featured_image, 'http')) {
            return $post->featured_image;
        }
        // publicUrl() = {host}/blog/{slug} → root is scheme+host
        $parts = parse_url($post->publicUrl());
        $root = ($parts['scheme'] ?? 'https').'://'.($parts['host'] ?? config('services.indexnow.host'));
        if (!empty($parts['port'])) $root .= ':'.$parts['port'];
        return $root.'/storage/'.$post->featured_image;
    }

    /** -----------------------------------------------------------------
     *  Admin "Test connection" — verifies credentials for one network
     *  without publishing anything.
     *  ----------------------------------------------------------------- */

    public function testNetwork(string $network): array
    {
        try {
            return match ($network) {
                'x'         => $this->testX(),
                'facebook'  => $this->testFacebook(),
                'linkedin'  => $this->testLinkedIn(),
                'instagram' => $this->testInstagram(),
                'telegram'  => $this->testTelegram(),
                'pinterest' => $this->testPinterest(),
                default     => ['ok' => false, 'error' => 'Unknown network'],
            };
        } catch (\Throwable $e) {
            return ['ok' => false, 'error' => $e->getMessage()];
        }
    }

    private function testX(): array
    {
        $ck  = $this->getCredential('x_consumer_key');
        $cs  = $this->getCredential('x_consumer_secret');
        $at  = $this->getCredential('x_access_token');
        $ats = $this->getCredential('x_access_token_secret');
        if (!$ck || !$cs || !$at || !$ats) return ['ok' => false, 'error' => 'All four X credentials are required.'];
        $auth = $this->oauth1Header('GET', 'https://api.twitter.com/2/users/me', [], $ck, $cs, $at, $ats);
        $r = Http::timeout(15)->withHeaders(['Authorization' => $auth])->get('https://api.twitter.com/2/users/me');
        return $r->successful()
            ? ['ok' => true, 'message' => 'Connected as @'.data_get($r->json(), 'data.username', '?')]
            : ['ok' => false, 'error' => 'X API '.$r->status().': '.$r->body()];
    }

    private function testFacebook(): array
    {
        $pageId = trim((string) Setting::get('facebook_page_id', ''));
        $token  = $this->getCredential('facebook_page_token');
        if (!$pageId || !$token) return ['ok' => false, 'error' => 'Page ID and Page Access Token are required.'];
        $r = Http::timeout(15)->get("https://graph.facebook.com/v19.0/{$pageId}", [
            'fields' => 'name', 'access_token' => $token,
        ]);
        return $r->successful()
            ? ['ok' => true, 'message' => 'Connected to page "'.$r->json('name').'"']
            : ['ok' => false, 'error' => 'Graph API '.$r->status().': '.$r->body()];
    }

    private function testLinkedIn(): array
    {
        $token = $this->getCredential('linkedin_access_token');
        if (!$token) return ['ok' => false, 'error' => 'Access token required.'];
        $r = Http::timeout(15)->withHeaders(['Authorization' => 'Bearer '.$token])
            ->get('https://api.linkedin.com/v2/userinfo');
        return $r->successful()
            ? ['ok' => true, 'message' => 'Token valid for '.($r->json('name') ?? $r->json('email') ?? 'member')]
            : ['ok' => false, 'error' => 'LinkedIn API '.$r->status().': '.$r->body()];
    }

    private function testInstagram(): array
    {
        $igUser = trim((string) Setting::get('instagram_user_id', ''));
        $token  = $this->getCredential('instagram_access_token');
        if (!$igUser || !$token) return ['ok' => false, 'error' => 'IG user ID and token required.'];
        $r = Http::timeout(15)->get("https://graph.facebook.com/v19.0/{$igUser}", [
            'fields' => 'username', 'access_token' => $token,
        ]);
        return $r->successful()
            ? ['ok' => true, 'message' => 'Connected IG business account @'.$r->json('username')]
            : ['ok' => false, 'error' => 'Graph API '.$r->status().': '.$r->body()];
    }

    private function testTelegram(): array
    {
        $token = $this->getCredential('telegram_bot_token');
        if (!$token) return ['ok' => false, 'error' => 'Bot token required.'];
        $r = Http::timeout(15)->get("https://api.telegram.org/bot{$token}/getMe");
        return $r->successful()
            ? ['ok' => true, 'message' => 'Bot @'.data_get($r->json(), 'result.username', '?').' reachable. Message the bot, add it to your channel, then save the chat ID.']
            : ['ok' => false, 'error' => 'Telegram API '.$r->status().': '.$r->body()];
    }

    private function testPinterest(): array
    {
        $token = $this->getCredential('pinterest_access_token');
        if (!$token) return ['ok' => false, 'error' => 'Access token required.'];
        $r = Http::timeout(15)->withHeaders(['Authorization' => 'Bearer '.$token])
            ->get('https://api.pinterest.com/v5/user_account');
        return $r->successful()
            ? ['ok' => true, 'message' => 'Connected as '.$r->json('username')]
            : ['ok' => false, 'error' => 'Pinterest API '.$r->status().': '.$r->body()];
    }
}

<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Post;
use App\Models\Faq;
use App\Services\ImageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Author dashboard — a stripped-down CMS for registered users.
 *
 * Routes (prefix: /author-dashboard, middleware: auth):
 *   GET  /                  -> index          (stats + recent posts)
 *   GET  /posts             -> postsIndex     (list my posts)
 *   GET  /posts/create      -> postsCreate    (new post form)
 *   POST /posts             -> postsStore     (save as draft)
 *   POST /posts/{id}/submit -> postsSubmit   (send for review)
 *   GET  /posts/{id}/edit   -> postsEdit      (edit returned/draft post)
 *   POST /posts/{id}        -> postsUpdate    (save edits)
 *   POST /posts/{id}        -> postsDestroy   (delete own post)
 *   GET  /profile           -> profileEdit    (username, bio, socials)
 *   POST /profile           -> profileUpdate
 *   GET  /monetization      -> monetization    (Coming Soon placeholder)
 *   GET  /posting-rules     -> rules          (static rules page)
 *   POST /account           -> accountDelete  (self-delete account)
 *
 * Constraints:
 *   - Users can ONLY see/edit their own posts here (scoped by auth()->id()).
 *   - Daily-1-post limit enforced in postsSubmit().
 *   - Post status never goes directly to published — only admin review can
 *     approve.
 */
class AuthorDashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $stats = [
            'total'      => Post::byAuthor($user->id)->count(),
            'draft'      => Post::byAuthor($user->id)->where('review_status', 'draft')->count(),
            'pending'    => Post::byAuthor($user->id)->where('review_status', 'pending_review')->count(),
            'published'  => Post::byAuthor($user->id)->where('review_status', 'approved')->count(),
            'returned'   => Post::byAuthor($user->id)->where('review_status', 'returned')->count(),
            'views'      => Post::byAuthor($user->id)->sum('views'),
            'can_submit' => !Post::authorSubmittedRecently($user->id),
            'next_submit_at' => Post::where('user_id', $user->id)
                ->whereNotNull('submitted_at')
                ->where('submitted_at', '>=', now()->subDay())
                ->orderBy('submitted_at')
                ->value('submitted_at')?->addDay(),
        ];
        $recent = Post::byAuthor($user->id)->with('category')->latest()->limit(5)->get();

        return view('frontend.author-dashboard.index', compact('stats', 'recent'));
    }

    public function postsIndex(Request $request)
    {
        $tab = $request->input('tab', 'all');
        $query = Post::byAuthor(auth()->id())->with('category');
        $query = match ($tab) {
            'draft'     => $query->where('review_status', 'draft'),
            'pending'   => $query->where('review_status', 'pending_review'),
            'published' => $query->where('review_status', 'approved'),
            'returned'  => $query->where('review_status', 'returned'),
            default     => $query,
        };
        $posts = $query->latest()->paginate(10)->withQueryString();

        $counts = [
            'all'       => Post::byAuthor(auth()->id())->count(),
            'draft'     => Post::byAuthor(auth()->id())->where('review_status', 'draft')->count(),
            'pending'   => Post::byAuthor(auth()->id())->where('review_status', 'pending_review')->count(),
            'published' => Post::byAuthor(auth()->id())->where('review_status', 'approved')->count(),
            'returned'  => Post::byAuthor(auth()->id())->where('review_status', 'returned')->count(),
        ];

        return view('frontend.author-dashboard.posts-index', compact('posts', 'counts', 'tab'));
    }

    public function postsCreate()
    {
        $categories = Category::where('is_active', true)->orderBy('sort_order')->get();
        return view('frontend.author-dashboard.posts-create', compact('categories'));
    }

    public function postsStore(Request $request)
    {
        $data = $request->validate([
            'title'           => ['required', 'string', 'max:255'],
            'excerpt'         => ['nullable', 'string', 'max:500'],
            'content'         => ['required', 'string', 'min:120'],
            'category_id'     => ['required', 'exists:categories,id'],
            'featured_image'  => ['nullable', 'image', 'max:4096'],
            'is_affiliate'    => ['nullable', 'boolean'],
            'meta_title'      => ['required', 'string', 'max:255'],
            'meta_description'=> ['required', 'string', 'max:500'],
            'meta_keywords'   => ['nullable', 'string', 'max:255'],
            'faqs'            => ['required', 'array', 'min:1'],
            'faqs.*.question' => ['nullable', 'string', 'max:500'],
            'faqs.*.answer'   => ['nullable', 'string', 'max:2000'],
            'action'          => ['required', 'in:save_draft,submit'],
        ]);

        // Word-count floor — minimum helpful content (anti-AI-slop rule).
        $wordCount = str_word_count(strip_tags($data['content']));
        if ($data['action'] === 'submit' && $wordCount < 300) {
            return back()->withErrors([
                'content' => "Submitted posts must contain at least 300 words (currently {$wordCount}). Lower-effort posts are rejected as 'thin content'.",
            ])->withInput();
        }


        // FAQ requirement: at least one COMPLETE question + answer pair.
        $hasCompleteFaq = collect($request->input('faqs', []))
            ->contains(fn ($f) => !empty($f['question']) && !empty($f['answer']));
        if (! $hasCompleteFaq) {
            return back()->withErrors([
                'faqs' => 'Add at least one FAQ with both a question and an answer.',
            ])->withInput();
        }
        $user = Auth::user();
        if ($data['action'] === 'submit' && Post::authorSubmittedRecently($user->id)) {
            return back()->withErrors([
                'submit' => 'Daily limit reached. You can submit one post for review per 24 hours.',
            ])->withInput();
        }

        $post = new Post();
        $post->title = $data['title'];
        $post->slug = $this->generateUniqueSlug($data['title']);
        $post->excerpt = $data['excerpt'] ?? null;
        $post->content = $data['content'];
        $post->category_id = $data['category_id'] ?? null;
        $post->user_id = $user->id;
        $post->author_name = $user->name;
        $post->author_bio = $user->bio;
        $post->author_avatar = $user->author_avatar_path;
        $post->reading_time = max(1, ceil($wordCount / 200));
        $post->meta_title = $data['meta_title'] ?? null;
        $post->meta_description = $data['meta_description'] ?? null;
        $post->meta_keywords = $data['meta_keywords'] ?? null;
        $post->is_affiliate = $request->boolean('is_affiliate');
        $post->status = 'draft';
        $post->review_status = $data['action'] === 'submit' ? 'pending_review' : 'draft';
        $post->submitted_at = $data['action'] === 'submit' ? now() : null;

        if ($request->hasFile('featured_image')) {
            $post->featured_image = app(ImageService::class)
                ->optimizeAndStore($request->file('featured_image'), 'uploads/posts');
        }

        $post->save();
        $this->syncFaqs($request, $post);

        if ($data['action'] === 'submit') {
            $this->notifyAdminsOfSubmission($post);
        }

        $msg = $data['action'] === 'submit'
            ? 'Post submitted for review. You can submit another post in 24 hours.'
            : 'Draft saved.';
        return redirect()->route('author.posts.index')->with('success', $msg);
    }

    public function postsEdit($id)
    {
        $post = $this->loadOwnPost($id);
        $post->load('faqs');
        // Active categories + the post's own (possibly disabled) category, so
        // editing never silently reassigns the category.
        $categories = Category::where('is_active', true)->orderBy('sort_order')->get();
        if ($post->category_id && !$categories->contains('id', $post->category_id) && $post->category) {
            $categories->prepend($post->category);
        }
        return view('frontend.author-dashboard.posts-edit', compact('post', 'categories'));
    }

    public function postsUpdate(Request $request, $id)
    {
        $post = $this->loadOwnPost($id);

        // Already-published or already-pending posts are read-only for the author.
        if (in_array($post->review_status, ['approved', 'pending_review'])) {
            return back()->withErrors([
                'action' => 'This post is locked. ' . match ($post->review_status) {
                    'approved' => 'It\'s already published — contact an admin to make changes.',
                    'pending_review' => 'It\'s awaiting admin review — wait for the decision before editing.',
                },
            ])->withInput();
        }

        $data = $request->validate([
            'title'           => ['required', 'string', 'max:255'],
            'excerpt'         => ['nullable', 'string', 'max:500'],
            'content'         => ['required', 'string', 'min:120'],
            'category_id'     => ['required', 'exists:categories,id'],
            'featured_image'  => ['nullable', 'image', 'max:4096'],
            'is_affiliate'    => ['nullable', 'boolean'],
            'meta_title'      => ['required', 'string', 'max:255'],
            'meta_description'=> ['required', 'string', 'max:500'],
            'meta_keywords'   => ['nullable', 'string', 'max:255'],
            'faqs'            => ['required', 'array', 'min:1'],
            'faqs.*.question' => ['nullable', 'string', 'max:500'],
            'faqs.*.answer'   => ['nullable', 'string', 'max:2000'],
            'action'          => ['required', 'in:save_draft,submit'],
        ]);

        $wordCount = str_word_count(strip_tags($data['content']));
        if ($data['action'] === 'submit' && $wordCount < 300) {
            return back()->withErrors([
                'content' => "Submitted posts must contain at least 300 words (currently {$wordCount}).",
            ])->withInput();
        }

        // If the post was returned by admin, the author must re-submit.
        // Resetting review_status to pending_review clears the reviewer note.
        if ($data['action'] === 'submit') {
            // Enforce daily limit ONLY when this is a fresh submission OR a
            // re-submission after a return — not when the author is editing
            // a post that's still in "draft".
            if (Post::authorSubmittedRecently(auth()->id())) {
                return back()->withErrors([
                    'submit' => 'Daily limit reached. You can submit one post per 24 hours.',
                ])->withInput();
            }
            $post->review_status = 'pending_review';
            $post->submitted_at = now();
            $post->reviewer_note = null;
        } else {
            $post->review_status = 'draft';
        }

        $post->title = $data['title'];
        $post->excerpt = $data['excerpt'] ?? null;
        $post->content = $data['content'];
        $post->category_id = $data['category_id'] ?? null;
        $post->meta_title = $data['meta_title'] ?? null;
        $post->meta_description = $data['meta_description'] ?? null;
        $post->meta_keywords = $data['meta_keywords'] ?? null;
        $post->is_affiliate = $request->boolean('is_affiliate');
        $post->reading_time = max(1, ceil($wordCount / 200));

        if ($request->hasFile('featured_image')) {
            if ($post->featured_image && !str_starts_with($post->featured_image, 'http')) {
                app(\App\Services\ImageService::class)->delete($post->featured_image);
            }
            $post->featured_image = app(ImageService::class)
                ->optimizeAndStore($request->file('featured_image'), 'uploads/posts');
        }

        $post->save();

        // FAQ requirement: at least one COMPLETE question + answer pair.
        $hasCompleteFaq = collect($request->input('faqs', []))
            ->contains(fn ($f) => !empty($f['question']) && !empty($f['answer']));
        if (! $hasCompleteFaq) {
            return back()->withErrors([
                'faqs' => 'Add at least one FAQ with both a question and an answer.',
            ])->withInput();
        }
        $this->syncFaqs($request, $post);

        if ($data['action'] === 'submit') {
            $this->notifyAdminsOfSubmission($post);
        }

        $msg = $data['action'] === 'submit'
            ? 'Updated and re-submitted for review.'
            : 'Draft saved.';
        return redirect()->route('author.posts.index')->with('success', $msg);
    }

    public function postsSubmit($id)
    {
        $post = $this->loadOwnPost($id);

        if ($post->review_status === 'approved' || $post->review_status === 'pending_review') {
            return back()->withErrors([
                'submit' => 'This post is already in the pipeline.',
            ]);
        }
        if (Post::authorSubmittedRecently(auth()->id())) {
            return back()->withErrors([
                'submit' => 'Daily limit reached. Wait 24 hours between submissions.',
            ]);
        }
        $wordCount = str_word_count(strip_tags($post->content));
        if ($wordCount < 300) {
            return back()->withErrors([
                'submit' => "Post must be at least 300 words (currently {$wordCount}). Add more substance before submitting.",
            ]);
        }

        $post->review_status = 'pending_review';
        $post->submitted_at = now();
        $post->reviewer_note = null;
        $post->save();

        $this->notifyAdminsOfSubmission($post);

        return redirect()->route('author.posts.index')
            ->with('success', 'Submitted for review. We\'ll notify you when it\'s processed.');
    }

    public function postsDestroy($id)
    {
        $post = $this->loadOwnPost($id);

        // Authors can't delete a post once it's published — ask admin.
        if ($post->review_status === 'approved') {
            return back()->withErrors([
                'delete' => 'Published posts can only be removed by an admin. Contact support if it must come down.',
            ]);
        }

        if ($post->featured_image && !str_starts_with($post->featured_image, 'http')) {
            app(\App\Services\ImageService::class)->delete($post->featured_image);
        }
        $post->faqs()->delete();
        $post->forceDelete();

        return redirect()->route('author.posts.index')->with('success', 'Post deleted.');
    }

    public function profileEdit()
    {
        $user = Auth::user();
        return view('frontend.author-dashboard.profile', compact('user'));
    }

    public function profileUpdate(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();

        $rules = [
            'name'             => ['required', 'string', 'max:60', 'regex:/^[\p{L}\p{M}\s.\-]+$/u'],
            'bio'              => ['required', 'string', 'min:30', 'max:600'],
            'avatar'           => ['nullable', 'image', 'max:4096'],
            'role_title'       => ['nullable', 'string', 'max:60'],
            'portfolio_url'    => ['nullable', 'url', 'max:255'],
            'social_links'     => ['nullable', 'array'],
            'social_links.*'   => ['nullable', 'url', 'max:255'],
        ];

        // Username is one-time-lockable — only allow setting it if it's
        // currently empty. Once set, it's read-only forever.
        if (empty($user->username)) {
            $rules['username'] = [
                'required', 'string', 'min:3', 'max:30',
                'regex:/^[a-z0-9._-]+$/i',
                'unique:users,username',
            ];
        }

        $data = $request->validate($rules, [
            'username.regex' => 'Username can only contain letters, numbers, dots, underscores and hyphens.',
            'username.unique' => 'That username is taken. Try another.',
        ]);

        $user->name = $data['name'];
        $user->bio = $data['bio'] ?? null;
        $user->role_title = $data['role_title'] ?? null;
        $user->portfolio_url = $data['portfolio_url'] ?? null;
        $user->social_links = array_filter($data['social_links'] ?? [], fn($u) => is_string($u) && $u !== '');

        if (empty($user->username) && isset($data['username'])) {
            // Lowercase the username for case-insensitive uniqueness on lookup
            $user->username = strtolower($data['username']);
        }

        if ($request->hasFile('avatar')) {
            try {
                $imageService = app(ImageService::class);
                // Remove the previous avatar from both storage locations.
                $imageService->delete($user->author_avatar_path);
                $path = $imageService->optimizeAndStore($request->file('avatar'), 'uploads/avatars');
                $user->author_avatar_path = $path;
                $user->avatar = $path;
            } catch (\Throwable $e) {
                // Save the rest of the profile, but tell the author what
                // happened to the photo instead of a 500 page.
                return back()->with('error', 'The photo could not be saved. ' . $e->getMessage());
            }
        }

        $user->save();

        return back()->with('success', 'Profile saved.');
    }

    public function revenue()
    {
        $user = Auth::user();

        // The revenue program is off by default; the admin turns it on from
        // Settings in the admin panel.
        $revenueEnabled = setting('revenue_enabled', '0') === '1';

        // Affiliate performance: clicks on outbound links inside the author's
        // affiliate posts, and the click rate against post views.
        try {
            $affiliatePostIds = Post::byAuthor($user->id)->where('is_affiliate', true)->pluck('id');
            $affiliateClicks = \App\Models\AffiliateClick::whereIn('post_id', $affiliatePostIds)->count();
            $affiliateViews = (int) Post::byAuthor($user->id)->where('is_affiliate', true)->sum('views');
        } catch (\Throwable $e) {
            $affiliatePostIds = collect();
            $affiliateClicks = 0;
            $affiliateViews = 0;
        }

        $clickRate = $affiliateViews > 0 ? round(($affiliateClicks / $affiliateViews) * 100, 1) : 0.0;

        $stats = [
            'total_views'    => (int) Post::byAuthor($user->id)->sum('views'),
            'published'      => Post::byAuthor($user->id)->where('review_status', 'approved')->count(),
            'affiliate_posts' => $affiliatePostIds->count(),
            'affiliate_clicks' => $affiliateClicks,
            'click_rate'     => $clickRate,
        ];

        return view('frontend.author-dashboard.revenue', compact('revenueEnabled', 'stats'));
    }

    public function rules()
    {
        return view('frontend.author-dashboard.rules');
    }

    public function accountDelete(Request $request)
    {
        $request->validate([
            'confirm' => ['required', 'accepted'],
            'password' => ['required', 'string'],
        ], [
            'confirm.accepted' => 'You must check the box to confirm deletion.',
            'password.required' => 'Enter your password to confirm.',
        ]);

        /** @var User $user */
        $user = Auth::user();

        if (!Hash::check($request->password, $user->password)) {
            return back()->withErrors(['password' => 'Wrong password.'])->onlyInput('password');
        }

        // Reassign posts to NULL user (they remain visible on the site if
        // already published; otherwise they're soft-deleted). This matches
        // WordPress "deleted users keep their published posts" behaviour.
        foreach (Post::byAuthor($user->id)->where('review_status', '!=', 'approved')->get() as $post) {
            $post->forceDelete();
        }
        Post::byAuthor($user->id)->update(['user_id' => null, 'author_name' => 'Former author']);

        // Wipe following relationships
        DB::table('user_follows')->where('follower_id', $user->id)->orWhere('followee_id', $user->id)->delete();

        Auth::logout();
        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')
            ->with('success', 'Your account has been deleted. Goodbye!');
    }

    // --- helpers ---

    private function loadOwnPost(int $id): Post
    {
        /** @var Post $post */
        $post = Post::byAuthor(auth()->id())->findOrFail($id);
        return $post;
    }

    /**
     * Notify every admin (role='admin') that a post was submitted for review.
     * Failures are swallowed (mail may not be configured on dev / shared hosting).
     */
    private function notifyAdminsOfSubmission(Post $post): void
    {
        try {
            $admins = \App\Models\User::where('role', 'admin')->get();
            foreach ($admins as $admin) {
                try { $admin->notify(new \App\Notifications\PostSubmittedForReview($post)); } catch (\Throwable $e) {
                    report($e);
                }
            }
        } catch (\Throwable $e) {
            report($e);
        }
    }

    private function generateUniqueSlug(string $title): string
    {
        $base = Str::slug($title);
        $slug = $base;
        $i = 1;
        while (Post::withTrashed()->where('slug', $slug)->exists()) {
            $slug = $base.'-'.$i++;
        }
        return $slug;
    }

    private function syncFaqs(Request $request, Post $post): void
    {
        $post->faqs()->delete();
        if ($request->filled('faqs')) {
            foreach ($request->faqs as $idx => $faq) {
                if (!empty($faq['question']) && !empty($faq['answer'])) {
                    $post->faqs()->create([
                        'question' => $faq['question'],
                        'answer'   => $faq['answer'],
                        'sort_order' => $idx,
                    ]);
                }
            }
        }
    }
}

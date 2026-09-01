<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Post;
use App\Models\Faq;
use App\Services\ImageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PostController extends Controller
{
    public function index(Request $request)
    {
        $tab = $request->input('tab', 'all');

        $query = match ($tab) {
            'draft'     => Post::query()->where('status', 'draft'),
            'published' => Post::query()->where('status', 'published')
                            ->where(fn($q) => $q->whereNull('scheduled_at')->orWhere('scheduled_at', '<=', now())),
            'scheduled' => Post::query()->where('scheduled_at', '>', now()),
            'trash'     => Post::onlyTrashed(),
            default     => Post::query(),
        };

        $query->with(['category', 'user']);
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(fn($q) => $q->where('title', 'like', "%{$s}%")->orWhere('slug', 'like', "%{$s}%"));
        }
        if ($request->filled('category')) $query->where('category_id', $request->category);

        $posts = $query->latest()->paginate(15)->withQueryString();

        $counts = [
            'all'       => Post::count(),
            'draft'     => Post::where('status', 'draft')->count(),
            'published' => Post::where('status', 'published')
                            ->where(fn($q) => $q->whereNull('scheduled_at')->orWhere('scheduled_at', '<=', now()))
                            ->count(),
            'scheduled' => Post::where('scheduled_at', '>', now())->count(),
            'trash'     => Post::onlyTrashed()->count(),
        ];

        $categories = Category::where('is_active', true)->orderBy('sort_order')->get();
        return view('admin.posts.index', compact('posts', 'categories', 'counts', 'tab'));
    }

    public function create()
    {
        $categories = Category::where('is_active', true)->orderBy('sort_order')->get();
        return view('admin.posts.create', compact('categories'));
    }

    /**
     * Server-side autosave endpoint (admin panel) — DRAFTS ONLY.
     *
     * Mirrors the author autosave: the editor silently POSTs the whole form
     * every 45 s (and once on tab close), and the payload becomes/updates a
     * real draft row. A browser crash, power cut or dropped network can never
     * lose more than 45 seconds of typing.
     *
     * Safety rules:
     *   - PUBLISHED or SCHEDULED posts are NEVER auto-mutated (a silent write
     *     to live content would be far worse than losing a little typing).
     *     Editing those is still covered by the browser's local snapshot.
     *   - Empty payloads are skipped to avoid draft spam.
     *   - All failures are quiet JSON responses — autosave must never
     *     interrupt writing.
     */
    public function autosave(Request $request)
    {
        try {
            $data = $request->validate([
                'autosave_post_id' => ['nullable', 'integer'],
                'title'            => ['nullable', 'string', 'max:255'],
                'excerpt'          => ['nullable', 'string', 'max:500'],
                'content'          => ['nullable', 'string'],
                'category_id'      => ['nullable', 'exists:categories,id'],
                'meta_title'       => ['nullable', 'string', 'max:255'],
                'meta_description' => ['nullable', 'string', 'max:500'],
                'meta_keywords'    => ['nullable', 'string', 'max:255'],
                'faqs'             => ['nullable', 'array'],
                'faqs.*.question'  => ['nullable', 'string', 'max:500'],
                'faqs.*.answer'    => ['nullable', 'string', 'max:2000'],
            ]);

            $post = null;
            if (!empty($data['autosave_post_id'])) {
                $post = Post::find((int) $data['autosave_post_id']);
                // Only DRAFT posts may be auto-mutated — a silent write to a
                // published/scheduled/live post would be a data-integrity bug.
                if ($post && ($post->status !== 'draft' || $post->review_status !== 'draft')) {
                    return response()->json([
                        'ok' => false,
                        'locked' => true,
                        'message' => 'Only draft posts can be auto-saved. Use Update Post to save changes to this post.',
                    ], 409);
                }
            }

            $title = trim((string) ($data['title'] ?? ''));
            $contentLen = mb_strlen(trim((string) ($data['content'] ?? '')));

            if (! $post) {
                if ($title === '' && $contentLen === 0) {
                    return response()->json(['ok' => true, 'skipped' => true]);
                }
                $post = new Post();
                $post->user_id = auth()->id();
                $post->status = 'draft';
                $post->review_status = 'draft';
                $post->allow_comments = true;
            }

            $post->title = mb_substr($title !== '' ? $title : 'Untitled draft', 0, 255);
            if (! $post->exists || empty($post->slug)) {
                $post->slug = $this->generateUniqueSlug($post->title, $post->exists ? $post->id : null);
            }
            if (array_key_exists('excerpt', $data)) {
                $post->excerpt = $data['excerpt'] !== null ? mb_substr((string) $data['excerpt'], 0, 500) : null;
            }
            if (array_key_exists('content', $data)) {
                $post->content = \App\Services\HtmlSanitizer::clean((string) ($data['content'] ?? ''));
            }
            if (array_key_exists('category_id', $data) && $data['category_id']) {
                $post->category_id = (int) $data['category_id'];
            }
            if (array_key_exists('meta_title', $data)) {
                $post->meta_title = $data['meta_title'] !== null ? mb_substr((string) $data['meta_title'], 0, 255) : null;
            }
            if (array_key_exists('meta_description', $data)) {
                $post->meta_description = $data['meta_description'] !== null ? mb_substr((string) $data['meta_description'], 0, 500) : null;
            }
            if (array_key_exists('meta_keywords', $data)) {
                $post->meta_keywords = $data['meta_keywords'] !== null ? mb_substr((string) $data['meta_keywords'], 0, 255) : null;
            }
            $post->reading_time = max(1, ceil(str_word_count(strip_tags((string) $post->content)) / 200));
            $post->autosaved_at = now();
            $post->save();

            if (array_key_exists('faqs', $data) && is_array($data['faqs'])) {
                try {
                    $this->syncFaqs($request, $post);
                } catch (\Throwable $e) {
                    report($e);
                }
            }

            return response()->json([
                'ok' => true,
                'autosave_post_id' => $post->id,
                'saved_at' => $post->autosaved_at->format('H:i'),
                // Bind by the MODEL (slug route key), not the numeric id —
                // admin.posts.edit uses {post} which resolves by slug.
                'edit_url' => route('admin.posts.edit', $post),
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['ok' => false, 'message' => 'Some fields could not be auto-saved.'], 200);
        } catch (\Throwable $e) {
            report($e);
            return response()->json(['ok' => false, 'message' => 'Auto-save failed. Your work is still kept in this browser.'], 200);
        }
    }

    protected function validationRules(Post $post = null): array
    {
        return [
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:posts,slug'.($post ? ','.$post->id : ''),
            'excerpt' => 'nullable|string|max:500',
            'content' => 'required|string',
            'category_id' => 'nullable|exists:categories,id',
            'featured_image' => 'nullable|image|max:4096',
            'status' => 'required|in:draft,published,archived',
            'scheduled_at' => 'nullable|date',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'meta_keywords' => 'nullable|string|max:255',
            'focus_keyword' => 'nullable|string|max:120',
            'faqs.*.question' => 'nullable|string|max:500',
            'faqs.*.answer' => 'nullable|string|max:2000',
        ];
    }

    public function store(Request $request)
    {
        $request->validate($this->validationRules());

        // If a server-side autosave already created a draft for this editing
        // session, "Create Post" UPDATES that draft instead of leaving a
        // near-duplicate "Untitled draft" row behind.
        $autosaveId = (int) $request->input('autosave_post_id');
        $existing = null;
        if ($autosaveId) {
            $candidate = Post::find($autosaveId);
            if ($candidate && $candidate->status === 'draft' && $candidate->review_status === 'draft') {
                $existing = $candidate;
            }
        }

        $data = $request->except(['featured_image', 'featured_image_url', 'faqs', 'scheduled_at', '_token', '_method']);
        $data['slug'] = $this->generateUniqueSlug($request->slug ? Str::slug($request->slug) : Str::slug($request->title), $existing?->id);
        $data['user_id'] = auth()->id();
        $data['is_featured'] = $request->boolean('is_featured');
        $data['allow_comments'] = $request->boolean('allow_comments');

        if ($request->hasFile('featured_image')) {
            try {
                $data['featured_image'] = app(ImageService::class)->optimizeAndStore($request->file('featured_image'), 'uploads/posts');
            } catch (\InvalidArgumentException $e) {
                return back()->withErrors(['featured_image' => $e->getMessage()])->withInput();
            } catch (\Throwable $e) {
                report($e);
                return back()->withErrors(['featured_image' => 'The featured image could not be uploaded (server storage problem). The post was NOT saved — please try a smaller JPG/PNG image or try again later.'])->withInput();
            }
        } elseif ($request->filled('featured_image_url')) {
            $data['featured_image'] = $request->featured_image_url;
        }

        $data['scheduled_at'] = $request->filled('scheduled_at') ? \Illuminate\Support\Carbon::parse($request->scheduled_at) : null;
        if ($data['status'] === 'published') {
            $isFuture = $data['scheduled_at'] && $data['scheduled_at']->isFuture();
            $data['published_at'] = $isFuture ? null : now();
        } else {
            $data['published_at'] = null;
        }
        $data['content'] = \App\Services\HtmlSanitizer::clean($data['content']);
        $data['reading_time'] = max(1, ceil(str_word_count(strip_tags($data['content'])) / 200));
        // Hard-cap lengths to what the database columns can store — prevents
        // "Data too long for column" 500 errors regardless of strict mode.
        $data['title'] = mb_substr((string) $data['title'], 0, 255);
        $data['slug'] = mb_substr((string) $data['slug'], 0, 255);
        $data['excerpt'] = isset($data['excerpt']) ? mb_substr((string) $data['excerpt'], 0, 500) : null;
        $data['meta_title'] = isset($data['meta_title']) ? mb_substr((string) $data['meta_title'], 0, 255) : null;
        $data['meta_description'] = isset($data['meta_description']) ? mb_substr((string) $data['meta_description'], 0, 500) : null;
        $data['meta_keywords'] = isset($data['meta_keywords']) ? mb_substr((string) $data['meta_keywords'], 0, 255) : null;
        // RankMath-style focus keyword + persisted SEO score (list badges).
        $data['focus_keyword'] = isset($data['focus_keyword']) ? mb_substr(trim((string) $data['focus_keyword']), 0, 120) ?: null : null;
        $data['seo_score'] = app(\App\Services\SeoAnalyzer::class)->analyze(
            $data['title'] ?? null, $data['meta_title'] ?? null, $data['meta_description'] ?? null,
            $data['focus_keyword'] ?? null, $data['slug'] ?? null, $data['content'] ?? null
        )['score'];

        if ($existing) {
            // Resuming an auto-saved draft: keep its author, overwrite the rest.
            $data['autosaved_at'] = null;
            $existing->fill($data);
            $existing->save();
            $post = $existing;
        } else {
            $post = Post::create($data);
        }
        $this->syncFaqs($request, $post);

        // Published now? Send the admin straight to the share screen with
        // the post URL + social share icons (and auto-post delivery status).
        if ($post->status === 'published') {
            return redirect()->route('admin.posts.share', $post)->with('success', 'Post published! Share it with the world:');
        }

        return redirect()->route('admin.posts.index')->with('success', 'Post created successfully');
    }

    public function edit(Post $post)
    {
        $post->load('faqs');
        // Active categories + the post's own (possibly disabled) category, so
        // editing never silently reassigns the category.
        $categories = Category::where('is_active', true)->orderBy('sort_order')->get();
        if ($post->category_id && !$categories->contains('id', $post->category_id) && $post->category) {
            $categories->prepend($post->category);
        }
        return view('admin.posts.edit', compact('post', 'categories'));
    }

    public function update(Request $request, Post $post)
    {
        $request->validate($this->validationRules($post));

        $data = $request->except(['featured_image', 'featured_image_url', 'faqs', 'scheduled_at', '_token', '_method']);
        $data['slug'] = $this->generateUniqueSlug($request->slug ? Str::slug($request->slug) : Str::slug($request->title), $post->id);
        $data['is_featured'] = $request->boolean('is_featured');
        $data['allow_comments'] = $request->boolean('allow_comments');
        if ($request->hasFile('featured_image')) {
            // Store the NEW image first, delete the old one after — a failed
            // upload then can never leave the post without its picture.
            $newImage = null;
            try {
                $newImage = app(ImageService::class)->optimizeAndStore($request->file('featured_image'), 'uploads/posts');
            } catch (\InvalidArgumentException $e) {
                return back()->withErrors(['featured_image' => $e->getMessage()])->withInput();
            } catch (\Throwable $e) {
                report($e);
                return back()->withErrors(['featured_image' => 'The featured image could not be uploaded (server storage problem). Your other changes were NOT saved — please try a smaller JPG/PNG image or try again later.'])->withInput();
            }
            if ($newImage) {
                if ($post->featured_image && !str_starts_with($post->featured_image, 'http')) {
                    app(\App\Services\ImageService::class)->delete($post->featured_image);
                }
                $data['featured_image'] = $newImage;
            }
        } elseif ($request->filled('featured_image_url')) {
            $data['featured_image'] = $request->featured_image_url;
        }

        $data['scheduled_at'] = $request->filled('scheduled_at') ? \Illuminate\Support\Carbon::parse($request->scheduled_at) : null;
        if ($data['status'] !== 'published') {
            $data['published_at'] = null;
        } else {
            $isFuture = $data['scheduled_at'] && $data['scheduled_at']->isFuture();
            $data['published_at'] = $isFuture ? $post->published_at : ($post->published_at ?: now());
        }

        // ------------------------------------------------------------------
        // Keep the AUTHOR-facing review state in sync with the admin publish
        // state. Bug this fixes: an author's post kept showing "In review"
        // in their dashboard forever, because the admin had approved it via
        // THIS regular edit form (status → published) instead of the review
        // queue — and this method never touched review_status.
        //
        //   published  → review_status 'approved'  (dashboard shows Published)
        //   unpublished→ an already-approved post goes back to 'draft' so the
        //                author can edit and re-submit (dashboard shows Draft,
        //                never a stale "Published" badge)
        // ------------------------------------------------------------------
        if ($data['status'] === 'published' && $post->review_status !== 'approved') {
            $data['review_status'] = 'approved';
            $data['reviewed_at']   = now();
            $data['reviewer_id']   = auth()->id();
        } elseif ($data['status'] !== 'published' && $post->review_status === 'approved') {
            $data['review_status'] = 'draft';
        }

        $data['title'] = mb_substr((string) $data['title'], 0, 255);
        $data['slug'] = mb_substr((string) $data['slug'], 0, 255);
        $data['excerpt'] = isset($data['excerpt']) ? mb_substr((string) $data['excerpt'], 0, 500) : null;
        $data['meta_title'] = isset($data['meta_title']) ? mb_substr((string) $data['meta_title'], 0, 255) : null;
        $data['meta_description'] = isset($data['meta_description']) ? mb_substr((string) $data['meta_description'], 0, 500) : null;
        $data['meta_keywords'] = isset($data['meta_keywords']) ? mb_substr((string) $data['meta_keywords'], 0, 255) : null;
        $data['focus_keyword'] = isset($data['focus_keyword']) ? mb_substr(trim((string) $data['focus_keyword']), 0, 120) ?: null : null;
        $data['seo_score'] = app(\App\Services\SeoAnalyzer::class)->analyze(
            $data['title'] ?? null, $data['meta_title'] ?? null, $data['meta_description'] ?? null,
            $data['focus_keyword'] ?? null, $data['slug'] ?? null, $data['content'] ?? null
        )['score'];

        // Manual update clears the autosave-only marker.
        $data['autosaved_at'] = null;
        $post->update($data);
        $this->syncFaqs($request, $post);

        if (($data['status'] ?? null) === 'published') {
            return redirect()->route('admin.posts.share', $post)->with('success', 'Post published! Share it with the world:');
        }

        return redirect()->route('admin.posts.index')->with('success', 'Post updated');
    }

    /**
     * Build a unique URL slug. Falls back to a random slug when the title is
     * non-Latin (Str::slug() returns '' for Arabic/Bengali/Chinese titles),
     * which previously crashed the second such save with a unique-constraint
     * 500. $ignoreId lets the current post keep its own slug on update.
     */
    protected function generateUniqueSlug(string $base, ?int $ignoreId = null): string
    {
        if ($base === '') {
            $base = 'post-'.strtolower(Str::random(8));
        }
        $base = mb_substr($base, 0, 240);
        $slug = $base;
        $i = 1;
        while (Post::withTrashed()->where('slug', $slug)->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))->exists()) {
            $slug = $base.'-'.$i++;
        }
        return $slug;
    }

    protected function syncFaqs(Request $request, Post $post): void
    {
        $post->faqs()->delete();
        if ($request->filled('faqs')) {
            foreach ($request->faqs as $idx => $faq) {
                if (!empty($faq['question']) && !empty($faq['answer'])) {
                    $post->faqs()->create([
                        'question' => mb_substr((string) $faq['question'], 0, 500),
                        'answer'   => mb_substr((string) $faq['answer'], 0, 2000),
                        'sort_order' => $idx,
                    ]);
                }
            }
        }
    }

    /**
     * Bulk actions for the posts list — the admin ticks any number of
     * checkboxes and moves/restores/deletes them in ONE click.
     *
     *   trash   → soft-delete the selected posts (move to trash)
     *   restore → restore the selected posts from trash
     *   delete  → permanently delete the selected posts (trash tab only),
     *             including their featured image files.
     *
     * SoftDeletes semantics keep this safe: "trash" only ever touches
     * non-trashed rows, "restore"/"delete" only ever touch trashed rows
     * (Post::onlyTrashed()), so a stale browser tab can never wrongly
     * restore a post the admin just trashed from another tab.
     */
    public function bulkAction(Request $request)
    {
        $data = $request->validate([
            'ids'         => ['required', 'array', 'min:1'],
            'ids.*'       => ['integer'],
            'bulk_action' => ['required', 'in:trash,restore,delete'],
        ]);

        $ids   = array_values(array_unique(array_map('intval', $data['ids'])));
        $count = 0;

        switch ($data['bulk_action']) {
            case 'trash':
                // Non-trashed posts only (SoftDeletes excludes trashed rows).
                $count = Post::whereIn('id', $ids)->count();
                if ($count) {
                    Post::whereIn('id', $ids)->delete();
                }
                $message = $count === 1 ? '1 post moved to trash' : $count.' posts moved to trash';
                break;

            case 'restore':
                $count = Post::onlyTrashed()->whereIn('id', $ids)->count();
                if ($count) {
                    Post::onlyTrashed()->whereIn('id', $ids)->restore();
                }
                $message = $count === 1 ? '1 post restored from trash' : $count.' posts restored from trash';
                break;

            default: // delete — permanently, trash tab only
                $posts = Post::onlyTrashed()->whereIn('id', $ids)->get();
                $count = $posts->count();
                foreach ($posts as $post) {
                    if ($post->featured_image && !str_starts_with($post->featured_image, 'http')) {
                        app(\App\Services\ImageService::class)->delete($post->featured_image);
                    }
                }
                if ($count) {
                    Post::onlyTrashed()->whereIn('id', $ids)->forceDelete();
                }
                $message = $count === 1 ? '1 post permanently deleted' : $count.' posts permanently deleted';
        }

        if ($count === 0) {
            $verbs = ['trash' => 'moved to trash', 'restore' => 'restored', 'delete' => 'deleted'];
            return back()->with('error', 'None of the selected posts could be '.$verbs[$data['bulk_action']].' — they may have already been removed.');
        }

        return back()->with('success', $message);
    }

    /** Soft delete — post moves to trash and disappears from the frontend. */
    public function destroy(Post $post)
    {
        $post->delete();
        return back()->with('success', 'Post moved to trash');
    }

    public function restore($id)
    {
        $post = Post::onlyTrashed()->findOrFail($id);
        $post->restore();
        return back()->with('success', 'Post restored from trash');
    }

    public function forceDelete($id)
    {
        $post = Post::onlyTrashed()->findOrFail($id);
        if ($post->featured_image && !str_starts_with($post->featured_image, 'http')) {
            app(\App\Services\ImageService::class)->delete($post->featured_image);
        }
        $post->forceDelete();
        return back()->with('success', 'Post permanently deleted');
    }

    public function toggleStatus(Post $post)
    {
        $post->status = $post->status === 'published' ? 'draft' : 'published';
        if ($post->status === 'published' && !$post->published_at) {
            // Keep scheduled-future posts without a publish date
            $isFuture = $post->scheduled_at && $post->scheduled_at->isFuture();
            if (!$isFuture) $post->published_at = now();
        }

        // Sync the AUTHOR-facing review state with the new publish state,
        // same rule as in update(): publishing marks the submission as
        // approved (otherwise the author's dashboard shows "In review"
        // forever even though the post is live), unpublishing a previously
        // approved post hands it back to the author as an editable draft.
        if ($post->status === 'published' && $post->review_status !== 'approved') {
            $post->review_status = 'approved';
            $post->reviewed_at   = now();
            $post->reviewer_id   = auth()->id();
        } elseif ($post->status !== 'published' && $post->review_status === 'approved') {
            $post->review_status = 'draft';
        }

        $post->save();
        return back()->with('success', 'Status updated to '.$post->status);
    }

    // ----------------------------------------------------------------
    //  Submission review workflow (multi-author posts)
    // ----------------------------------------------------------------

    /**
     * List posts submitted by non-admin authors that are awaiting review.
     * Tab = "pending" shows review_status='pending_review'.
     * Tab = "returned" shows the ones the admin returned earlier.
     */
    public function reviewQueue(Request $request)
    {
        // Guard: if the review_status column doesn't exist yet (migration
        // not run), redirect to a helpful message instead of a 500 error.
        try {
            if (!\Illuminate\Support\Facades\Schema::hasColumn('posts', 'review_status')) {
                return redirect()->route('admin.dashboard')
                    ->with('error', 'The review workflow table is not set up yet. Reload the admin dashboard once to apply pending database migrations.');
            }
        } catch (\Throwable $e) {
            // If even Schema check fails, show the queue as empty.
            return view('admin.posts.review-queue', [
                'posts' => new \Illuminate\Pagination\LengthAwarePaginator([], 0, 15),
                'counts' => ['pending' => 0, 'returned' => 0, 'approved' => 0],
                'tab' => $request->input('tab', 'pending'),
            ]);
        }

        $tab = $request->input('tab', 'pending');
        $query = Post::query()->with(['category', 'user']);
        $query = match ($tab) {
            'pending'   => $query->where('review_status', 'pending_review'),
            'returned'  => $query->where('review_status', 'returned'),
            'approved'  => $query->where('review_status', 'approved'),
            default     => $query->whereIn('review_status', ['pending_review', 'returned']),
        };
        $posts = $query->latest()->paginate(15)->withQueryString();
        $counts = [
            'pending'   => Post::where('review_status', 'pending_review')->count(),
            'returned'  => Post::where('review_status', 'returned')->count(),
            'approved'  => Post::where('review_status', 'approved')->count(),
        ];
        return view('admin.posts.review-queue', compact('posts', 'counts', 'tab'));
    }

    /**
     * Approve a submitted post — admin CAN modify content first (the form
     * on the review page lets the admin edit title/excerpt/content/SEO),
     * and then approve & publish in one click.
     */
    public function approve(Request $request, Post $post)
    {
        $request->validate([
            'title'           => ['required', 'string', 'max:255'],
            'excerpt'         => ['nullable', 'string', 'max:500'],
            'content'         => ['required', 'string'],
            'category_id'     => ['nullable', 'exists:categories,id'],
            'is_featured'     => ['nullable', 'boolean'],
            'is_affiliate'    => ['nullable', 'boolean'],
            'allow_comments'  => ['nullable', 'boolean'],
            'meta_title'      => ['nullable', 'string', 'max:255'],
            'meta_description'=> ['nullable', 'string', 'max:500'],
            'focus_keyword'   => ['nullable', 'string', 'max:120'],
            'reviewer_note'   => ['nullable', 'string', 'max:500'],
            'faqs'            => ['nullable', 'array'],
            'faqs.*.question' => ['nullable', 'string', 'max:500'],
            'faqs.*.answer'   => ['nullable', 'string', 'max:2000'],
        ]);

        // Apply admin's edits directly to the post.
        $post->fill($request->only([
            'title', 'excerpt', 'category_id',
            'meta_title', 'meta_description',
        ]));
        $post->content        = \App\Services\HtmlSanitizer::clean((string) $request->content);
        $post->is_featured    = $request->boolean('is_featured');
        $post->is_affiliate   = $request->boolean('is_affiliate');
        $post->allow_comments = $request->boolean('allow_comments');
        $post->reading_time   = max(1, ceil(str_word_count(strip_tags($request->content)) / 200));

        $post->review_status  = 'approved';
        $post->reviewed_at    = now();
        $post->reviewer_id    = auth()->id();
        $post->status         = 'published';
        $post->published_at   = $post->published_at ?: now();
        $post->focus_keyword  = $request->filled('focus_keyword') ? mb_substr(trim((string) $request->focus_keyword), 0, 120) : $post->focus_keyword;
        $post->seo_score      = app(\App\Services\SeoAnalyzer::class)->analyze(
            $post->title, $post->meta_title, $post->meta_description,
            $post->focus_keyword, $post->slug, $post->content
        )['score'];

        $post->save();
        $this->syncFaqs($request, $post);

        // Notify the author that their post is now live.
        if ($post->user_id && $post->user) {
            try { $post->user->notify(new \App\Notifications\PostApproved($post)); } catch (\Throwable $e) {
                // Mail config may not be set up — silently skip on failure.
                report($e);
            }
        }

        // Straight to the share screen: URL + share icons + auto-post status.
        return redirect()->route('admin.posts.share', $post)
            ->with('success', 'Post approved and published. Share it:');
    }

    /**
     * Return a submitted post to the author for revision, with a short note
     * explaining what needs to change. The post reverts to review_status='returned'
     * and is removed from the admin queue.
     */
    public function return(Request $request, Post $post)
    {
        $validated = $request->validate([
            'reviewer_note' => ['required', 'string', 'max:500'],
        ], [
            'reviewer_note.required' => 'You must explain what the author should change.',
        ]);

        $post->review_status = 'returned';
        $post->reviewed_at = now();
        $post->reviewer_id = auth()->id();
        $post->reviewer_note = $validated['reviewer_note'];
        $post->save();

        // Notify the author with the reviewer's note.
        if ($post->user_id && $post->user) {
            try { $post->user->notify(new \App\Notifications\PostReturned($post)); } catch (\Throwable $e) {
                report($e);
            }
        }

        return redirect()->route('admin.posts.review-queue')
            ->with('success', 'Post returned to the author with your note.');
    }

    // ---------------------------------------------------------------------
    // Instant index + post-publish share screen
    // ---------------------------------------------------------------------

    /**
     * Manual instant-index ping (Posts list button). IndexNow auto-pings on
     * every save already; this lets the admin force a refetch (e.g. after a
     * big edit, a 404 fix or for posts published before IndexNow existed).
     */
    public function instantIndex(Post $post)
    {
        $service = app(\App\Services\IndexNowService::class);
        $ok = $service->submit([$service->postUrl($post->slug)]);

        if ($ok) {
            try { $post->forceFill(['instant_indexed_at' => now()])->saveQuietly(); } catch (\Throwable $e) {}
            return back()->with('success', 'Ping sent to Bing, Yandex, Seznam & Naver for "'.$post->title.'".');
        }
        return back()->with('error', 'IndexNow ping failed — check connectivity and try again.');
    }

    /** Bulk instant index: every currently-visible published post. */
    public function bulkInstantIndex()
    {
        $service = app(\App\Services\IndexNowService::class);
        $posts = Post::where('status', 'published')
            ->where(fn ($q) => $q->whereNull('scheduled_at')->orWhere('scheduled_at', '<=', now()))
            ->get();

        if ($posts->isEmpty()) {
            return back()->with('error', 'No published posts to index.');
        }
        // IndexNow accepts up to 10,000 URLs per call — one call does it.
        $ok = $service->submit($posts->map(fn ($p) => $service->postUrl($p->slug))->all());
        if ($ok) {
            $stamps = [];
            foreach ($posts as $p) $stamps[] = ['id' => $p->id, 'instant_indexed_at' => now()];
            try {
                foreach ($stamps as $s) {
                    Post::where('id', $s['id'])->forceFill(['instant_indexed_at' => $s['instant_indexed_at']])->saveQuietly();
                }
            } catch (\Throwable $e) {}
            return back()->with('success', 'Submitted '.$posts->count().' post URLs to IndexNow.');
        }
        return back()->with('error', 'IndexNow bulk submission failed — try again shortly.');
    }

    /**
     * Post-publish share screen: the post URL in a big copyable box plus
     * social share icons (and the social auto-post delivery log when the
     * automation ran for this post).
     */
    public function share(Post $post)
    {
        $post->load(['socialPublishes' => fn ($q) => $q->latest('updated_at')]);
        $service = app(\App\Services\Social\SocialAutoPostService::class);
        $autopostEnabled = $service->enabled();
        $activeNetworks = $service->activeNetworks();

        return view('admin.posts.share', [
            'post'            => $post,
            'shareUrl'        => $post->publicUrl(),
            'autopostEnabled' => $autopostEnabled,
            'activeNetworks'  => $activeNetworks,
        ]);
    }
}

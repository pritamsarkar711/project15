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

        $categories = Category::orderBy('sort_order')->get();
        return view('admin.posts.index', compact('posts', 'categories', 'counts', 'tab'));
    }

    public function create()
    {
        $categories = Category::orderBy('sort_order')->get();
        return view('admin.posts.create', compact('categories'));
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
            'faqs.*.question' => 'nullable|string|max:500',
            'faqs.*.answer' => 'nullable|string|max:2000',
        ];
    }

    public function store(Request $request)
    {
        $request->validate($this->validationRules());

        $data = $request->except(['featured_image', 'featured_image_url', 'faqs', 'scheduled_at', '_token', '_method']);
        $data['slug'] = $request->slug ? Str::slug($request->slug) : Str::slug($request->title);
        $original = $data['slug'];
        $i = 1;
        while (Post::withTrashed()->where('slug', $data['slug'])->exists()) {
            $data['slug'] = $original.'-'.$i++;
        }
        $data['user_id'] = auth()->id();
        $data['is_featured'] = $request->boolean('is_featured');
        $data['allow_comments'] = $request->boolean('allow_comments');

        if ($request->hasFile('featured_image')) {
            $data['featured_image'] = app(ImageService::class)->optimizeAndStore($request->file('featured_image'), 'uploads/posts');
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
        $data['reading_time'] = max(1, ceil(str_word_count(strip_tags($data['content'])) / 200));

        $post = Post::create($data);
        $this->syncFaqs($request, $post);

        return redirect()->route('admin.posts.index')->with('success', 'Post created successfully');
    }

    public function edit(Post $post)
    {
        $post->load('faqs');
        $categories = Category::orderBy('sort_order')->get();
        return view('admin.posts.edit', compact('post', 'categories'));
    }

    public function update(Request $request, Post $post)
    {
        $request->validate($this->validationRules($post));

        $data = $request->except(['featured_image', 'featured_image_url', 'faqs', 'scheduled_at', '_token', '_method']);
        $data['slug'] = $request->slug ? Str::slug($request->slug) : Str::slug($request->title);
        $data['is_featured'] = $request->boolean('is_featured');
        $data['allow_comments'] = $request->boolean('allow_comments');
        if ($request->hasFile('featured_image')) {
            if ($post->featured_image && !str_starts_with($post->featured_image, 'http')) {
                Storage::disk('public')->delete($post->featured_image);
            }
            $data['featured_image'] = app(ImageService::class)->optimizeAndStore($request->file('featured_image'), 'uploads/posts');
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

        $post->update($data);
        $this->syncFaqs($request, $post);

        return redirect()->route('admin.posts.index')->with('success', 'Post updated');
    }

    protected function syncFaqs(Request $request, Post $post): void
    {
        $post->faqs()->delete();
        if ($request->filled('faqs')) {
            foreach ($request->faqs as $idx => $faq) {
                if (!empty($faq['question']) && !empty($faq['answer'])) {
                    $post->faqs()->create(['question' => $faq['question'], 'answer' => $faq['answer'], 'sort_order' => $idx]);
                }
            }
        }
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
            Storage::disk('public')->delete($post->featured_image);
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
        $post->save();
        return back()->with('success', 'Status updated to '.$post->status);
    }
}

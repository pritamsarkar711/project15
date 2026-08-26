<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Comment;
use App\Models\Post;
use App\Models\PostReaction;
use App\Models\User;
use Illuminate\Http\Request;

class BlogController extends Controller
{
    public function index(Request $request)
    {
        $query = Post::published()->with(['category','user']);
        if ($request->filled('category')) {
            $query->whereHas('category', fn($q)=>$q->where('slug',$request->category));
        }
        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function($qq) use ($q){
                $qq->where('title','like',"%{$q}%")->orWhere('excerpt','like',"%{$q}%");
            });
        }
        $posts = $query->latest('published_at')->paginate(12)->withQueryString();
        // Filter dropdown: only live categories (active + has published posts)
        try {
            $categories = Category::live()->orderBy('sort_order')->get();
        } catch (\Throwable $e) {
            $categories = collect();
        }
        try {
            $featured = Post::published()->where('is_featured',true)->latest()->take(3)->get();
        } catch (\Throwable $e) {
            $featured = collect();
        }
        return view('frontend.blog.index', compact('posts','categories','featured'));
    }

    public function show($slug)
    {
        $post = Post::published()->where('slug',$slug)->with(['category','user','faqs','approvedComments'])->firstOrFail();
        // increment views
        $post->increment('views');
        $related = Post::published()->where('category_id',$post->category_id)->where('id','!=',$post->id)->inRandomOrder()->take(3)->get();
        // Popular posts for the sidebar (most viewed across the whole site).
        $popular = Post::published()->where('id','!=',$post->id)->orderByDesc('views')->take(3)->get();
        $toc = $post->table_of_contents;
        // Extract headings for TOC rendering with ids injection
        // Defense in depth: legacy rows may predate save-time sanitizing.
        $contentWithAnchors = $this->injectAnchors(\App\Services\HtmlSanitizer::clean((string) $post->content), $toc);
        // Insert in-article ads every N paragraphs (default 2).
        // Ads only ever render when the admin has switched them on
        // (Settings, Ads tab). Until then content stays completely clean.
        if (setting('ads_enabled') !== '1') {
            $contentWithAnchors = $this->injectInArticleAds($contentWithAnchors, 0, null);
        } else {
            $adFrequency = (int) setting('ad_paragraph_frequency', 2);
            if ($adFrequency < 1) $adFrequency = 2;
            $inArticleAd = \App\Models\Advertisement::active()->position('in_article')->first();
            $contentWithAnchors = $this->injectInArticleAds($contentWithAnchors, $adFrequency, $inArticleAd);
        }

        // Reactions ("Did you like this post?" section after content + FAQ).
        // Defensive: if the post_reactions table has not been created yet (the
        // migration runs automatically, but a very fresh deploy may serve one
        // request before it finishes), fall back to zeros instead of throwing
        // a 500 error on every post page.
        try {
            $likesCount = $post->likes()->count();
            $dislikesCount = $post->dislikes()->count();
            $myReaction = auth()->check()
                ? $post->reactions()->where('user_id', auth()->id())->value('reaction')
                : null;
        } catch (\Throwable $e) {
            $likesCount = 0;
            $dislikesCount = 0;
            $myReaction = null;
        }

        // Does the current viewer follow this post's author? (Follow button
        // in the author box below the content.)
        $followingAuthor = auth()->check() && $post->user
            ? auth()->user()->following()->where('followee_id', $post->user->id)->exists()
            : false;

        return view('frontend.blog.show', compact('post','related','popular','toc','contentWithAnchors','likesCount','dislikesCount','myReaction','followingAuthor'));
    }

    /**
     * Toggle the viewer's like / dislike reaction on a post.
     *   - same button clicked again  → reaction removed
     *   - other button clicked       → reaction switched
     */
    public function react(Request $request, $slug)
    {
        $post = Post::published()->where('slug', $slug)->firstOrFail();
        $request->validate(['reaction' => 'required|in:like,dislike']);

        try {
            $existing = PostReaction::where('post_id', $post->id)
                ->where('user_id', auth()->id())
                ->first();

            if ($existing && $existing->reaction === $request->reaction) {
                $existing->delete();              // clicked the same button again → remove
            } else {
                PostReaction::updateOrCreate(
                    ['post_id' => $post->id, 'user_id' => auth()->id()],
                    ['reaction' => $request->reaction]
                );
            }
        } catch (\Throwable $e) {
            // Table not created yet (migration pending) — ignore gracefully.
        }

        return back();
    }

    /**
     * Track a click on an outbound (affiliate / external) link inside a post.
     * Called by JS on the post page. Stores only hashed identifiers, no raw
     * URL or IP, and is fully failure-tolerant: tracking problems never
     * affect the visitor.
     */
    public function trackClick(Request $request, $slug)
    {
        try {
            $post = Post::published()->where('slug', $slug)->first();
            if (!$post) {
                return response()->json(['ok' => false], 404);
            }
            \App\Models\AffiliateClick::create([
                'post_id' => $post->id,
                'user_id' => auth()->id(),
                'url_hash' => hash('sha256', (string) $request->input('url', '')),
                'ip_hash' => hash('sha256', $request->ip() ?? ''),
            ]);
        } catch (\Throwable $e) {
            // Never surface tracking errors to the visitor.
        }
        return response()->json(['ok' => true]);
    }

    /**
     * Insert an in-article ad after every N paragraphs in the post content.
     * The ad HTML is wrapped so it cannot break surrounding paragraphs.
     * If no active in-article ad exists or its code is empty, content is returned unchanged.
     */
    private function injectInArticleAds(string $content, int $frequency, $ad): string
    {
        if (!$ad) return $content;
        $code = trim((string) ($ad->code ?? ''));
        if ($code === '' || trim(strip_tags($code)) === '') return $content;

        // Split on closing </p> (case-insensitive). Keeps the closing tag on each chunk.
        $parts = preg_split('/(<\/p>)/i', $content, -1, PREG_SPLIT_DELIM_CAPTURE);
        if (count($parts) < 3) return $content;

        // Insert in-article ads after every N paragraphs in the post content.
        // The wrapper carries NO background or border on purpose: when the ad
        // network serves an empty unit (site not yet approved, no fill), the
        // slot collapses invisibly instead of showing an ugly blank box.
        $adBlock = '<div class="ad-slot my-8 text-center" style="min-height:0">'.$code.'</div>';
        $out = '';
        $paragraphsSinceAd = 0;
        $i = 0;
        while ($i < count($parts)) {
            $chunk = $parts[$i];
            // Is this the closing </p> delimiter?
            if (preg_match('/^<\/p>$/i', $chunk)) {
                $out .= $chunk;
                $paragraphsSinceAd++;
                // After every N paragraphs (and not at the very end of content), insert ad
                if ($paragraphsSinceAd >= $frequency && $i + 1 < count($parts)) {
                    // Check if there's meaningful content remaining (avoid trailing ad)
                    $remaining = implode('', array_slice($parts, $i + 1));
                    if (trim(strip_tags($remaining)) !== '') {
                        $out .= $adBlock;
                        $paragraphsSinceAd = 0;
                    }
                }
            } else {
                $out .= $chunk;
            }
            $i++;
        }
        return $out;
    }

    public function category($slug)
    {
        // Inactive categories are hidden from the public site entirely.
        $category = Category::where('slug',$slug)->where('is_active',true)->firstOrFail();
        $posts = Post::published()->where('category_id',$category->id)->latest('published_at')->paginate(12);
        return view('frontend.category.show', compact('category','posts'));
    }

    /**
     * Public author profile page at /author/{username}.
     * Shows: avatar, name, verified badge (for admins), role/title, bio,
     * portfolio link, social icons, follower count, follow button (if
     * a logged-in user is viewing), and the author's published posts.
     */
    public function authorProfile($username)
    {
        $author = User::where('username', $username)->firstOrFail();
        $posts = $author->publishedPosts()->with('category')->paginate(9)->withQueryString();
        $socials = $author->socialProfiles();
        $isFollowing = auth()->check()
            ? $author->followers()->where('follower_id', auth()->id())->exists()
            : false;

        // Visitor-visible stats: published posts only (drafts are private),
        // plus the total likes / dislikes this author's content received.
        // Defensive: same missing-table guard as in show().
        $publishedIds = $author->publishedPosts()->pluck('id');
        try {
            $totalLikes = PostReaction::whereIn('post_id', $publishedIds)->where('reaction', 'like')->count();
            $totalDislikes = PostReaction::whereIn('post_id', $publishedIds)->where('reaction', 'dislike')->count();
        } catch (\Throwable $e) {
            $totalLikes = 0;
            $totalDislikes = 0;
        }
        $publishedCount = $publishedIds->count();

        return view('frontend.author.profile', compact(
            'author', 'posts', 'socials', 'isFollowing', 'totalLikes', 'totalDislikes', 'publishedCount'
        ));
    }

    /**
     * Toggle follow on an author. If the logged-in user already follows the
     * author, unfollow; otherwise follow. Updates denormalized follower /
     * following counts on both users atomically via a transaction.
     */
    public function follow($username)
    {
        $author = User::where('username', $username)->firstOrFail();
        $me = auth()->user();

        if ($me->id === $author->id) {
            return back()->with('error', 'You cannot follow yourself.');
        }

        \DB::transaction(function () use ($me, $author) {
            $exists = $me->following()->where('followee_id', $author->id)->exists();
            if ($exists) {
                $me->following()->detach($author->id);
                $author->decrement('followers_count');
                $me->decrement('following_count');
            } else {
                $me->following()->syncWithoutDetaching([$author->id]);
                $author->increment('followers_count');
                $me->increment('following_count');
            }
        });

        return back()->with('success', auth()->user()->following()->where('followee_id', $author->id)->exists() ? 'You are now following '.$author->name : 'Unfollowed '.$author->name);
    }

    private function injectAnchors($content, $toc)
    {
        $index = 0;
        return preg_replace_callback('/<h([2-3])([^>]*)>(.*?)<\/h[2-3]>/i', function($m) use (&$index, $toc){
            $id = $toc[$index]['id'] ?? 'heading-'.$index;
            $index++;
            return "<h{$m[1]} id=\"{$id}\"{$m[2]}>{$m[3]}</h{$m[1]}>";
        }, $content);
    }

    public function storeComment(Request $request, $slug)
    {
        $post = Post::published()->where('slug',$slug)->firstOrFail();
        if (!$post->allow_comments) {
            return back()->with('error','Comments are disabled for this post.');
        }
        $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email|max:255',
            'content' => 'required|string|max:2000',
            'parent_id' => 'nullable|integer|exists:comments,id',
        ]);

        // Threaded replies: max 1 nesting level (deeper replies flatten onto the top-level parent)
        $parentId = null;
        $replyDepth = 0;
        if ($request->filled('parent_id')) {
            $parent = Comment::where('id', $request->parent_id)->where('post_id', $post->id)->first();
            if (!$parent) {
                return back()->with('error','The comment you are replying to no longer exists.');
            }
            if ((int) $parent->reply_depth >= 1) {
                $parentId = $parent->parent_id;
                $replyDepth = 1;
            } else {
                $parentId = $parent->id;
                $replyDepth = (int) $parent->reply_depth + 1;
            }
        }

        Comment::create([
            'post_id' => $post->id,
            'name' => $request->name,
            'email' => $request->email,
            'content' => $request->content,
            'status' => 'pending',
            'parent_id' => $parentId,
            'reply_depth' => $replyDepth,
            'ip_address' => $request->ip(),
        ]);
        return back()->with('success','Your comment is awaiting moderation. Thank you!');
    }
}

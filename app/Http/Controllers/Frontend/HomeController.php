<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Post;
use App\Models\Advertisement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        // Search bypasses everything
        if ($search = $request->query('q')) {
            return $this->search($request);
        }

        // We don't cache Eloquent Collections here — the database cache driver
        // serialises them and the unserialised objects come back as
        // __PHP_Incomplete_Class, which breaks `$cat->slug` access in views.
        // The queries below are already eager-loaded, so direct retrieval is
        // fast enough for a small/medium SQLite-backed blog.
        //
        // "Live" categories = enabled in admin AND have at least one published
        // post — empty categories stay hidden until their first post goes live.
        $categories = Category::live()
            ->orderBy('sort_order')
            ->withCount('posts')
            ->get();

        $featuredIds = Post::published()->where('is_featured', true)->pluck('id');

        $latestPosts = Post::published()
            ->with(['category','user'])
            ->whereNotIn('id', $featuredIds)
            ->latest('published_at')
            ->take(8)
            ->get();

        $featuredPosts = Post::published()
            ->where('is_featured', true)
            ->with('category')
            ->latest()
            ->take(3)
            ->get();

        $trending = Post::published()
            ->orderByDesc('views')
            ->take(4)
            ->get();

        $sidebarAd = Advertisement::active()->position('sidebar')->first();
        $inlineAd = Advertisement::active()->position('in_article')->first();

        return view('frontend.home', compact('categories','latestPosts','featuredPosts','trending','sidebarAd','inlineAd','search'));
    }

    public function search(Request $request)
    {
        $q = $request->query('q');
        $posts = Post::published()->where(function($query) use ($q){
            $query->where('title','like',"%{$q}%")->orWhere('excerpt','like',"%{$q}%")->orWhere('content','like',"%{$q}%");
        })->with('category')->latest()->paginate(9);
        $categories = Category::live()->orderBy('sort_order')->get();
        return view('frontend.search', compact('posts','q','categories'));
    }
}

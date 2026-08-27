<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Support\Facades\Schema;

class TopContributorsController extends Controller
{
    public function index()
    {
        // Admin feature switch (Settings -> General -> Features). When the
        // feature is off the page answers 404 - the SEO-correct response, so
        // search engines remove the URL instead of indexing a stub page.
        if (Setting::get('top_contributors_enabled', '1') !== '1') {
            abort(404);
        }

        $contributors = collect();
        try {
            if (Schema::hasTable('users') && Schema::hasTable('posts')) {
                $contributors = User::query()
                    ->select(['id','name','username','role_title','bio','author_avatar_path','is_verified'])
                    ->where('role', '!=', 'admin')
                    ->whereHas('posts', fn ($q) => $q->where('status', 'published')->whereNotNull('published_at'))
                    ->withCount(['posts' => fn ($q) => $q->where('status', 'published')->whereNotNull('published_at')])
                    ->orderByDesc('posts_count')
                    ->take(20)
                    ->get();
            }
        } catch (\Throwable $e) {
            $contributors = collect();
        }

        return view('frontend.top-contributors', compact('contributors'));
    }
}

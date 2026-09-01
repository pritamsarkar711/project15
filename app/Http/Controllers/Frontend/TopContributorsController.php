<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Schema;

class TopContributorsController extends Controller
{
    public function index()
    {
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

        return response()->view('frontend.top-contributors', compact('contributors'), 200);
    }
}

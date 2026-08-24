<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\ContactMessage;
use App\Models\Post;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'posts'            => Post::count(),
            'views'            => (int) Post::sum('views'),
            'comments_pending' => Comment::where('status', 'pending')->count(),
            'contact_unread'   => ContactMessage::where('is_read', false)->count(),
        ];

        $recentPosts = Post::with('category')->latest()->take(5)->get();
        $recentComments = Comment::with('post')->latest()->take(5)->get();

        return view('admin.dashboard', compact('stats', 'recentPosts', 'recentComments'));
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->input('status');

        // Top-level comments with their replies (one nesting level)
        $query = Comment::with(['post' => fn ($q) => $q->withTrashed()])->whereNull('parent_id')->latest();
        if ($status && in_array($status, ['pending', 'approved', 'rejected', 'spam'])) {
            $query->where(function ($q) use ($status) {
                $q->where('status', $status)
                  ->orWhereHas('replies', fn($r) => $r->where('status', $status));
            });
        }

        $comments = $query->paginate(20)->withQueryString();

        $counts = [
            'all'      => Comment::whereNull('parent_id')->count(),
            'pending'  => Comment::where('status', 'pending')->count(),
            'approved' => Comment::where('status', 'approved')->count(),
            'rejected' => Comment::where('status', 'rejected')->count(),
            'spam'     => Comment::where('status', 'spam')->count(),
        ];

        return view('admin.comments.index', compact('comments', 'counts'));
    }

    public function updateStatus(Request $request, Comment $comment)
    {
        $request->validate(['status' => 'required|in:pending,approved,rejected,spam']);
        $comment->update(['status' => $request->status]);
        return back()->with('success', 'Comment marked as '.$request->status);
    }

    public function destroy(Comment $comment)
    {
        // Deleting a parent removes its replies (FK cascade)
        $comment->delete();
        return back()->with('success', 'Comment deleted');
    }

    /**
     * Bulk delete: remove any number of selected comments (and their replies,
     * via the FK cascade) in one action. Used by the checkboxes in the
     * comments list.
     */
    public function bulkDestroy(Request $request)
    {
        $request->validate([
            'ids'   => 'required|array|min:1',
            'ids.*' => 'integer|exists:comments,id',
        ]);

        $count = Comment::whereIn('id', $request->input('ids'))->count();
        Comment::whereIn('id', $request->input('ids'))->delete();

        $message = $count === 1 ? '1 comment deleted' : $count.' comments deleted';
        if ($request->input('ids') && count($request->input('ids')) > $count) {
            $message .= ' (some were already removed)';
        }

        return back()->with('success', $message);
    }
}

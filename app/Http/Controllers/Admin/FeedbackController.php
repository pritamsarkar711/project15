<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Feedback;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class FeedbackController extends Controller
{
    public function index()
    {
        if (!Schema::hasTable('feedbacks')) {
            $feedbacks = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 20, 1, ['path' => request()->url()]);
            return view('admin.feedback.index', compact('feedbacks'));
        }
        $feedbacks = Feedback::with('user')->latest()->paginate(20);
        return view('admin.feedback.index', compact('feedbacks'));
    }

    public function show(Feedback $feedback)
    {
        $feedback->load('user');
        return view('admin.feedback.show', compact('feedback'));
    }

    public function destroy(Feedback $feedback)
    {
        $feedback->delete();
        return back()->with('success', 'Feedback removed.');
    }
}

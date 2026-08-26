<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Feedback;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class FeedbackController extends Controller
{
    public function index()
    {
        if (!Schema::hasTable('feedbacks')) {
            $feedbacks = collect();
            return view('frontend.author-dashboard.feedback', compact('feedbacks'));
        }
        $feedbacks = Feedback::where('user_id', auth()->id())->latest()->take(5)->get();
        return view('frontend.author-dashboard.feedback', compact('feedbacks'));
    }

    public function store(Request $request)
    {
        if (!Schema::hasTable('feedbacks')) {
            try {
                Schema::create('feedbacks', function ($table) {
                    $table->id();
                    $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
                    $table->string('overall_experience', 30)->nullable();
                    $table->string('profile_ease', 30)->nullable();
                    $table->string('publishing_ease', 30)->nullable();
                    $table->text('bug_report')->nullable();
                    $table->text('what_you_like')->nullable();
                    $table->text('what_to_improve')->nullable();
                    $table->text('feature_request')->nullable();
                    $table->text('additional_comment')->nullable();
                    $table->timestamps();
                });
            } catch (\Throwable $e) {
                return back()->with('error', 'Feedback system is being prepared. Please try again in a moment.');
            }
        }

        $data = $request->validate([
            'overall_experience' => 'required|string|max:30',
            'profile_ease' => 'required|string|max:30',
            'publishing_ease' => 'required|string|max:30',
            'bug_report' => 'nullable|string|max:3000',
            'what_you_like' => 'nullable|string|max:3000',
            'what_to_improve' => 'nullable|string|max:3000',
            'feature_request' => 'nullable|string|max:3000',
            'additional_comment' => 'nullable|string|max:3000',
        ]);

        $data['user_id'] = auth()->id();
        Feedback::create($data);

        return back()->with('success', 'Thank you. Your feedback has been received.');
    }
}

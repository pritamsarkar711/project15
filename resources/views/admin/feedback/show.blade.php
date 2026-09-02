@extends('layouts.admin')
@section('title','Feedback detail')
@section('content')
<div class="w-full">
    <a href="{{ route('admin.feedback.index') }}" class="text-sm text-[var(--brand-ink)] dark:text-[var(--brand-light)] hover:underline">Back to feedback</a>
    <div class="mt-4 panel-card p-6">
        <div class="flex items-start justify-between gap-4">
            <div>
                <div class="text-lg font-bold text-slate-900 dark:text-white">{{ $feedback->user->name ?? 'Unknown' }}</div>
                <div class="text-sm text-slate-500 dark:text-slate-400"><span class="break-all">{{ $feedback->user->email ?? '' }}</span> · {{ $feedback->created_at->format('M d, Y H:i') }}</div>
            </div>
            <form method="POST" action="{{ route('admin.feedback.destroy', $feedback) }}" onsubmit="return confirm('Remove this feedback?')">
                @csrf @method('DELETE')
                <button class="text-xs font-semibold text-red-600 dark:text-red-400 hover:underline">Delete</button>
            </form>
        </div>

        <div class="mt-6 grid sm:grid-cols-3 gap-4">
            <div class="border border-slate-100 dark:border-slate-800 bg-slate-50 dark:bg-slate-800/50 p-4">
                <div class="text-xs font-semibold tracking-wide uppercase text-slate-500 dark:text-slate-400">Overall experience</div>
                <div class="mt-1 font-semibold text-slate-900 dark:text-white">{{ $feedback->overall_experience ?? 'Not given' }}</div>
            </div>
            <div class="border border-slate-100 dark:border-slate-800 bg-slate-50 dark:bg-slate-800/50 p-4">
                <div class="text-xs font-semibold tracking-wide uppercase text-slate-500 dark:text-slate-400">Profile ease</div>
                <div class="mt-1 font-semibold text-slate-900 dark:text-white">{{ $feedback->profile_ease ?? 'Not given' }}</div>
            </div>
            <div class="border border-slate-100 dark:border-slate-800 bg-slate-50 dark:bg-slate-800/50 p-4">
                <div class="text-xs font-semibold tracking-wide uppercase text-slate-500 dark:text-slate-400">Publishing ease</div>
                <div class="mt-1 font-semibold text-slate-900 dark:text-white">{{ $feedback->publishing_ease ?? 'Not given' }}</div>
            </div>
        </div>

        <div class="mt-6 space-y-4 text-sm leading-relaxed">
            @foreach(['bug_report' => 'Bug report', 'what_you_like' => 'What you like most', 'what_to_improve' => 'What should we improve', 'feature_request' => 'Feature request', 'additional_comment' => 'Other comment'] as $field => $label)
                @if($feedback->$field)
                    <div class="border border-slate-100 dark:border-slate-800 p-4">
                        <div class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide">{{ $label }}</div>
                        <div class="mt-1 text-slate-700 dark:text-slate-300 whitespace-pre-wrap">{{ $feedback->$field }}</div>
                    </div>
                @endif
            @endforeach
        </div>
    </div>
</div>
@endsection

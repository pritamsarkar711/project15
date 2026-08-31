@extends('layouts.admin')
@section('title','Feedback')
@section('content')
<div class="w-full">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-xl font-bold text-slate-900 dark:text-white">Feedback</h1>
        <span class="text-xs font-medium bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 px-2.5 py-1">{{ $feedbacks->total() }} total</span>
    </div>

    @if($feedbacks->count())
        <div class="panel-card overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 dark:bg-slate-800/50 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide">
                        <tr>
                            <th class="text-left px-4 py-3">User</th>
                            <th class="text-left px-4 py-3">Overall</th>
                            <th class="text-left px-4 py-3">Profile</th>
                            <th class="text-left px-4 py-3">Publishing</th>
                            <th class="text-left px-4 py-3">Date</th>
                            <th class="text-right px-4 py-3">View</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                        @foreach($feedbacks as $fb)
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50">
                                <td class="px-4 py-3">
                                    <div class="font-medium text-slate-900 dark:text-white">{{ $fb->user->name ?? 'Unknown' }}</div>
                                    <div class="text-xs text-slate-500 dark:text-slate-400">{{ $fb->user->email ?? '' }}</div>
                                </td>
                                <td class="px-4 py-3"><span class="px-2 py-1 bg-[#F0F7F3] dark:bg-[#57A37E]/10 text-[#1F513A] dark:text-[#6FB393] text-xs font-medium">{{ $fb->overall_experience }}</span></td>
                                <td class="px-4 py-3 text-slate-700 dark:text-slate-300">{{ $fb->profile_ease }}</td>
                                <td class="px-4 py-3 text-slate-700 dark:text-slate-300">{{ $fb->publishing_ease }}</td>
                                <td class="px-4 py-3 text-slate-500 dark:text-slate-400 text-xs">{{ $fb->created_at->format('M d, Y') }}</td>
                                <td class="px-4 py-3 text-right"><a href="{{ route('admin.feedback.show', $fb) }}" class="text-[#1F513A] dark:text-[#6FB393] hover:underline text-xs font-semibold">View</a></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        <div class="mt-4">{{ $feedbacks->links() }}</div>
    @else
        <div class="panel-card p-10 text-center">
            <p class="text-sm text-slate-500 dark:text-slate-400">No feedback yet.</p>
        </div>
    @endif
</div>
@endsection

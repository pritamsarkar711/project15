@extends('frontend.author-dashboard.layout')

@section('title', 'Feedback')

@section('content')
<div class="w-full">
    <div class="border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-6 sm:p-8">
        <div class="flex items-start gap-4">
            <div class="w-12 h-12 bg-[#0C3B2E] text-white flex items-center justify-center shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7 12h.01M12 12h.01M17 12h.01M21 14a4 4 0 0 1-4 4H8a4 4 0 0 1-4-4V8a4 4 0 0 1 4-4h12a4 4 0 0 1 4 4z"/></svg>
            </div>
            <div>
                <h2 class="text-xl font-bold text-slate-900 dark:text-white">Feedback</h2>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1 leading-relaxed">Share your experience with Huvanti. Your answers help us fix issues and plan what to build next. This takes about two minutes.</p>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="mt-5 bg-emerald-50 dark:bg-emerald-400/10 border border-emerald-200 dark:border-emerald-500/20 text-emerald-800 dark:text-emerald-300 px-4 py-3 text-sm">{{ session('success') }}</div>
    @endif

    <form method="POST" action="{{ route('author.feedback.store') }}" class="mt-5 border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-6 space-y-6">
        @csrf
        <div>
            <label class="block text-sm font-semibold text-slate-900 dark:text-white mb-2">How would you rate your overall experience</label>
            <div class="grid grid-cols-2 sm:grid-cols-5 gap-2">
                @foreach(['Poor','Fair','Good','Very good','Excellent'] as $opt)
                    <label class="flex items-center gap-2 border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 px-3 py-2.5 cursor-pointer hover:border-[#0C3B2E] dark:hover:border-emerald-500 has-[input:checked]:border-[#0C3B2E] has-[input:checked]:bg-emerald-50 dark:has-[input:checked]:bg-emerald-400/10 transition">
                        <input type="radio" name="overall_experience" value="{{ $opt }}" required class="text-[#0C3B2E]">
                        <span class="text-sm text-slate-700 dark:text-slate-300">{{ $opt }}</span>
                    </label>
                @endforeach
            </div>
        </div>

        <div class="grid sm:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-semibold text-slate-900 dark:text-white mb-2">How easy was it to manage your profile</label>
                <select name="profile_ease" required class="w-full h-11 px-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-sm text-slate-900 dark:text-white focus:border-emerald-500 outline-none">
                    <option value="">Select one</option>
                    <option value="Very hard">Very hard</option>
                    <option value="Hard">Hard</option>
                    <option value="Okay">Okay</option>
                    <option value="Easy">Easy</option>
                    <option value="Very easy">Very easy</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-900 dark:text-white mb-2">How easy was it to write and publish a post</label>
                <select name="publishing_ease" required class="w-full h-11 px-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-sm text-slate-900 dark:text-white focus:border-emerald-500 outline-none">
                    <option value="">Select one</option>
                    <option value="Very hard">Very hard</option>
                    <option value="Hard">Hard</option>
                    <option value="Okay">Okay</option>
                    <option value="Easy">Easy</option>
                    <option value="Very easy">Very easy</option>
                </select>
            </div>
        </div>

        <div>
            <label class="block text-sm font-semibold text-slate-900 dark:text-white mb-2">Did you face any bug or error <span class="font-normal text-slate-500">Optional</span></label>
            <textarea name="bug_report" rows="3" maxlength="2000" placeholder="Describe what happened and where" class="w-full p-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-sm text-slate-900 dark:text-white focus:border-emerald-500 outline-none">{{ old('bug_report') }}</textarea>
        </div>

        <div>
            <label class="block text-sm font-semibold text-slate-900 dark:text-white mb-2">What do you like most</label>
            <textarea name="what_you_like" rows="2" maxlength="2000" placeholder="Tell us what works well for you" class="w-full p-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-sm text-slate-900 dark:text-white focus:border-emerald-500 outline-none">{{ old('what_you_like') }}</textarea>
        </div>

        <div>
            <label class="block text-sm font-semibold text-slate-900 dark:text-white mb-2">What should we improve</label>
            <textarea name="what_to_improve" rows="2" maxlength="2000" placeholder="Tell us what needs fixing" class="w-full p-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-sm text-slate-900 dark:text-white focus:border-emerald-500 outline-none">{{ old('what_to_improve') }}</textarea>
        </div>

        <div>
            <label class="block text-sm font-semibold text-slate-900 dark:text-white mb-2">Which feature would help you most <span class="font-normal text-slate-500">Optional</span></label>
            <textarea name="feature_request" rows="2" maxlength="2000" placeholder="Suggest a new feature or improvement" class="w-full p-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-sm text-slate-900 dark:text-white focus:border-emerald-500 outline-none">{{ old('feature_request') }}</textarea>
        </div>

        <div>
            <label class="block text-sm font-semibold text-slate-900 dark:text-white mb-2">Any other comment <span class="font-normal text-slate-500">Optional</span></label>
            <textarea name="additional_comment" rows="2" maxlength="2000" placeholder="Add anything else you want us to know" class="w-full p-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-sm text-slate-900 dark:text-white focus:border-emerald-500 outline-none">{{ old('additional_comment') }}</textarea>
        </div>

        <button type="submit" class="h-11 px-8 bg-[#0C3B2E] hover:bg-[#072A20] text-white font-semibold text-sm transition">Send feedback</button>
    </form>

    @if($feedbacks->count())
        <div class="mt-8 border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-6">
            <h3 class="font-semibold text-slate-900 dark:text-white">Your recent feedback</h3>
            <div class="mt-4 space-y-3">
                @foreach($feedbacks as $fb)
                    <div class="border border-slate-100 dark:border-slate-800 bg-slate-50 dark:bg-slate-800/50 p-3 text-sm">
                        <div class="flex items-center justify-between text-xs text-slate-500 dark:text-slate-400">
                            <span>{{ $fb->created_at->format('M d, Y') }}</span>
                            <span>{{ $fb->overall_experience }}</span>
                        </div>
                        @if($fb->what_you_like)<p class="mt-1 text-slate-700 dark:text-slate-300">{{ Str::limit($fb->what_you_like, 120) }}</p>@endif
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>
@endsection

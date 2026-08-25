@extends('frontend.author-dashboard.layout')

@section('title', 'Posting Rules')

@section('content')
<div class="max-w-[900px]">
    <div class="border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-6 sm:p-8">
        <div class="flex items-start gap-4">
            <div class="w-12 h-12 bg-[#0C3B2E] text-white flex items-center justify-center shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25"/></svg>
            </div>
            <div>
                <h2 class="text-xl font-bold text-slate-900 dark:text-white">Posting Rules</h2>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Every post is checked by an admin before it goes live.</p>
            </div>
        </div>
    </div>

    <div class="grid sm:grid-cols-2 gap-4 mt-5">
        <div class="border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-5">
            <div class="flex items-center gap-3">
                <span class="w-8 h-8 bg-emerald-50 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-300 flex items-center justify-center text-sm font-bold">1</span>
                <h3 class="font-semibold text-slate-900 dark:text-white">Write your own words</h3>
            </div>
            <p class="text-sm text-slate-600 dark:text-slate-400 mt-3 leading-relaxed">Write like you talk. Do not copy from other websites and do not publish text a bot clearly wrote. Copied or spammy posts are rejected.</p>
        </div>

        <div class="border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-5">
            <div class="flex items-center gap-3">
                <span class="w-8 h-8 bg-emerald-50 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-300 flex items-center justify-center text-sm font-bold">2</span>
                <h3 class="font-semibold text-slate-900 dark:text-white">Be useful</h3>
            </div>
            <p class="text-sm text-slate-600 dark:text-slate-400 mt-3 leading-relaxed">Solve a real problem for the reader. A tight 500 words beats a padded 1500. Minimum 300 words to submit.</p>
        </div>

        <div class="border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-5">
            <div class="flex items-center gap-3">
                <span class="w-8 h-8 bg-emerald-50 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-300 flex items-center justify-center text-sm font-bold">3</span>
                <h3 class="font-semibold text-slate-900 dark:text-white">Check your facts</h3>
            </div>
            <p class="text-sm text-slate-600 dark:text-slate-400 mt-3 leading-relaxed">Only claim what you can back up. Share your real experience. Never pretend to be an expert on something you do not know.</p>
        </div>

        <div class="border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-5">
            <div class="flex items-center gap-3">
                <span class="w-8 h-8 bg-emerald-50 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-300 flex items-center justify-center text-sm font-bold">4</span>
                <h3 class="font-semibold text-slate-900 dark:text-white">Fill the FAQ and SEO boxes</h3>
            </div>
            <p class="text-sm text-slate-600 dark:text-slate-400 mt-3 leading-relaxed">Every post needs at least one FAQ and a meta title with a description. These help readers and search engines.</p>
        </div>

        <div class="border border-red-200 dark:border-red-500/30 bg-white dark:bg-slate-900 p-5 sm:col-span-2">
            <div class="flex items-center gap-3">
                <span class="w-8 h-8 bg-red-50 dark:bg-red-500/10 text-red-600 dark:text-red-300 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 0 1 1.242 7.244l-4.5 4.5a4.5 4.5 0 0 1-6.364-6.364l1.757-1.757m13.35-.622 1.757-1.757a4.5 4.5 0 0 0-6.364-6.364l-4.5 4.5a4.5 4.5 0 0 0 1.242 7.244"/></svg>
                </span>
                <h3 class="font-semibold text-slate-900 dark:text-white">External link rules (important)</h3>
            </div>
            <ul class="mt-3 space-y-2 text-sm text-slate-600 dark:text-slate-400 leading-relaxed">
                <li class="flex gap-2"><svg class="w-4 h-4 text-red-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>Never link to adult, gambling, betting, drugs, hacking, piracy or any unsafe website.</li>
                <li class="flex gap-2"><svg class="w-4 h-4 text-red-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>Never link to unknown, low quality or shady sites just to earn a commission.</li>
                <li class="flex gap-2"><svg class="w-4 h-4 text-emerald-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>Affiliate links are welcome only for reputable sites readers know and trust, such as major retailers and well known brands.</li>
                <li class="flex gap-2"><svg class="w-4 h-4 text-emerald-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>Turn on the affiliate switch when your post has affiliate links. The disclosure notice is added for you.</li>
            </ul>
            <p class="text-sm font-semibold text-red-600 dark:text-red-400 mt-3">A post that breaks the link rules is rejected and the account may be removed.</p>
        </div>

        <div class="border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-5">
            <div class="flex items-center gap-3">
                <span class="w-8 h-8 bg-emerald-50 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-300 flex items-center justify-center text-sm font-bold">5</span>
                <h3 class="font-semibold text-slate-900 dark:text-white">One submission per day</h3>
            </div>
            <p class="text-sm text-slate-600 dark:text-slate-400 mt-3 leading-relaxed">Submit one post every 24 hours. Save as many drafts as you like.</p>
        </div>

        <div class="border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-5">
            <div class="flex items-center gap-3">
                <span class="w-8 h-8 bg-emerald-50 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-300 flex items-center justify-center text-sm font-bold">6</span>
                <h3 class="font-semibold text-slate-900 dark:text-white">How review works</h3>
            </div>
            <p class="text-sm text-slate-600 dark:text-slate-400 mt-3 leading-relaxed">The admin approves, edits or returns your post with a note. Fix the note and submit again. Published posts lock automatically.</p>
        </div>
    </div>

    <div class="mt-6 flex flex-wrap items-center gap-3">
        <a href="{{ route('author.posts.create') }}" class="inline-flex items-center gap-2 h-11 px-6 bg-[#0C3B2E] hover:bg-[#072A20] text-white font-semibold text-sm transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Z"/></svg>
            Start writing
        </a>
        <a href="{{ route('author.dashboard') }}" class="inline-flex items-center gap-2 h-11 px-6 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800 font-semibold text-sm transition">
            Back to dashboard
        </a>
    </div>
</div>
@endsection

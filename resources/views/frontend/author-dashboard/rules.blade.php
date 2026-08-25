@extends('frontend.author-dashboard.layout')

@section('title', 'Posting Rules')

@section('content')
<div class="w-full">
    <div class="border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-6 sm:p-8">
        <div class="flex items-start gap-4">
            <div class="w-12 h-12 bg-[#0C3B2E] text-white flex items-center justify-center shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z"/></svg>
            </div>
            <div>
                <h2 class="text-xl font-bold text-slate-900 dark:text-white">Posting Rules</h2>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1 leading-relaxed">Every post is reviewed by an admin before it goes live. Follow these rules to be published quickly. Posts that break them are returned with a note or removed.</p>
            </div>
        </div>
    </div>

    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4 mt-5">
        <div class="border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-5">
            <div class="flex items-center gap-3">
                <span class="w-9 h-9 bg-emerald-50 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-300 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232 18.5 8.5M9 13.5 5.5 17 3 18l1-2.5L9 10.5V13.5Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M12 8.25V12l2.25 1.5"/></svg>
                </span>
                <h3 class="font-semibold text-slate-900 dark:text-white">Write for real readers</h3>
            </div>
            <p class="text-sm text-slate-600 dark:text-slate-400 mt-3 leading-relaxed">Use your own words and experience. The final post must sound like a person wrote it. Generic filler and repetitive phrasing will be returned for a rewrite.</p>
        </div>

        <div class="border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-5">
            <div class="flex items-center gap-3">
                <span class="w-9 h-9 bg-emerald-50 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-300 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6"/><path stroke-linecap="round" stroke-linejoin="round" d="M3 12h.01M21 12h.01"/><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v1M12 20v1"/><circle cx="12" cy="12" r="9"/></svg>
                </span>
                <h3 class="font-semibold text-slate-900 dark:text-white">Keep it focused</h3>
            </div>
            <p class="text-sm text-slate-600 dark:text-slate-400 mt-3 leading-relaxed">Make one clear point and support it. A concise post with real value outperforms a long one padded with repeated ideas.</p>
        </div>

        <div class="border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-5">
            <div class="flex items-center gap-3">
                <span class="w-9 h-9 bg-emerald-50 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-300 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                </span>
                <h3 class="font-semibold text-slate-900 dark:text-white">Be useful</h3>
            </div>
            <p class="text-sm text-slate-600 dark:text-slate-400 mt-3 leading-relaxed">Answer a question the reader actually has. Share concrete steps, picks or examples rather than vague overviews.</p>
        </div>

        <div class="border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-5">
            <div class="flex items-center gap-3">
                <span class="w-9 h-9 bg-emerald-50 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-300 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M12 16.5h.008v.008H12v-.008Z"/></svg>
                </span>
                <h3 class="font-semibold text-slate-900 dark:text-white">Support what you say</h3>
            </div>
            <p class="text-sm text-slate-600 dark:text-slate-400 mt-3 leading-relaxed">Cite sources where relevant and write from genuine knowledge. Do not present uncertain claims as fact.</p>
        </div>

        <div class="border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-5">
            <div class="flex items-center gap-3">
                <span class="w-9 h-9 bg-emerald-50 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-300 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3.75v.75m0 12v.75m5.303-11.03-1.06 1.06M5.757 18.243l-1.06-1.06M18.364 18.243l-1.06-1.06M5.757 5.757l1.06 1.06M12 9.75a2.25 2.25 0 1 0 0 4.5 2.25 2.25 0 0 0 0-4.5Z"/></svg>
                </span>
                <h3 class="font-semibold text-slate-900 dark:text-white">Publish original work</h3>
            </div>
            <p class="text-sm text-slate-600 dark:text-slate-400 mt-3 leading-relaxed">All content must be yours. Copied text, repackaged press releases or keyword filled writing leads to permanent removal.</p>
        </div>

        <div class="border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-5">
            <div class="flex items-center gap-3">
                <span class="w-9 h-9 bg-emerald-50 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-300 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9.75 3.75h4.5m-4.5 16.5h4.5M5.25 12h13.5"/><circle cx="12" cy="12" r="9"/></svg>
                </span>
                <h3 class="font-semibold text-slate-900 dark:text-white">Complete the extras</h3>
            </div>
            <p class="text-sm text-slate-600 dark:text-slate-400 mt-3 leading-relaxed">Add at least one FAQ and fill in the meta title and description. This helps readers and search engines understand your topic.</p>
        </div>

        <div class="border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-5">
            <div class="flex items-center gap-3">
                <span class="w-9 h-9 bg-emerald-50 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-300 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 0 0 3 8.25v6.75A2.25 2.25 0 0 0 5.25 18h9c.41 0 .793-.11 1.125-.302L21 21V8.25A2.25 2.25 0 0 0 18.75 6H13.5Z"/></svg>
                </span>
                <h3 class="font-semibold text-slate-900 dark:text-white">Affiliate links</h3>
            </div>
            <p class="text-sm text-slate-600 dark:text-slate-400 mt-3 leading-relaxed">When a post includes affiliate links, turn on the affiliate toggle before submitting. The required disclosure is then added for you.</p>
        </div>

        <div class="border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-5">
            <div class="flex items-center gap-3">
                <span class="w-9 h-9 bg-emerald-50 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-300 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                </span>
                <h3 class="font-semibold text-slate-900 dark:text-white">Submission pace</h3>
            </div>
            <p class="text-sm text-slate-600 dark:text-slate-400 mt-3 leading-relaxed">You can submit one post for review every 24 hours. Drafts are unlimited. Use the time to refine your next draft.</p>
        </div>

        <div class="border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-5">
            <div class="flex items-center gap-3">
                <span class="w-9 h-9 bg-emerald-50 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-300 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5.586a1 1 0 0 1 .707.293l5.414 5.414a1 1 0 0 1 .293.707V19a2 2 0 0 1-2 2Z"/></svg>
                </span>
                <h3 class="font-semibold text-slate-900 dark:text-white">How review works</h3>
            </div>
            <p class="text-sm text-slate-600 dark:text-slate-400 mt-3 leading-relaxed">Submitted posts queue for admin review. An admin may approve, edit or return a post with a note. Address the note and resubmit. Published posts are then locked for further edits.</p>
        </div>
    </div>

    <div class="border border-red-200 dark:border-red-500/30 bg-white dark:bg-slate-900 p-5 mt-4">
        <div class="flex items-center gap-3">
            <span class="w-9 h-9 bg-red-50 dark:bg-red-500/10 text-red-600 dark:text-red-300 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z"/></svg>
            </span>
            <h3 class="font-semibold text-slate-900 dark:text-white">External link policy</h3>
        </div>
        <ul class="mt-4 space-y-2.5 text-sm text-slate-600 dark:text-slate-400 leading-relaxed">
            <li class="flex gap-2.5"><span class="w-5 h-5 bg-red-50 dark:bg-red-500/10 text-red-500 flex items-center justify-center shrink-0 mt-0.5"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg></span>Never link to adult, gambling, betting, drugs, hacking, piracy or any unsafe destination.</li>
            <li class="flex gap-2.5"><span class="w-5 h-5 bg-red-50 dark:bg-red-500/10 text-red-500 flex items-center justify-center shrink-0 mt-0.5"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg></span>Never link to unfamiliar, low quality or questionable sites for the sake of a commission.</li>
            <li class="flex gap-2.5"><span class="w-5 h-5 bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600 flex items-center justify-center shrink-0 mt-0.5"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg></span>Affiliate links are welcome only from reputable sources that readers already trust, such as major retailers and well known brands.</li>
        </ul>
        <p class="text-sm font-semibold text-red-600 dark:text-red-400 mt-4">A post that breaks the link policy is rejected and the account may be removed.</p>
    </div>

    <div class="border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900/60 p-5 mt-4 text-sm text-slate-600 dark:text-slate-400 leading-relaxed">
        <span class="font-semibold text-slate-900 dark:text-white">About your account</span>
        You can delete it at any time from your <a href="{{ route('author.profile.edit') }}" class="text-emerald-700 dark:text-emerald-300 hover:underline font-semibold">Profile</a> page. Drafts and returned posts are removed with it, while published posts stay online under a former author name.
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

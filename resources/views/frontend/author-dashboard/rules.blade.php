@extends('frontend.author-dashboard.layout')

@section('title', 'Posting Rules')

@section('content')
<div class="w-full">
    <div class="panel-card p-6 sm:p-8">
        <div class="flex items-start gap-4">
            <div class="w-12 h-12 bg-[#0C3B2E] text-white flex items-center justify-center shrink-0">
                <svg class="w-6 h-6 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5.586a1 1 0 0 1 .707.293l5.414 5.414a1 1 0 0 1 .293.707V19a2 2 0 0 1-2 2Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M9 5a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v2a2 2 0 0 1-2 2H9a2 2 0 0 1-2-2V5Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M12 12h.01M12 16h.01"/></svg>
            </div>
            <div>
                <h2 class="text-xl font-bold text-slate-900 dark:text-white">Posting Rules</h2>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1 leading-relaxed">Every post is reviewed by an admin before it goes live. Follow these rules to be published quickly. Posts that break them are returned with a note or removed.</p>
            </div>
        </div>
    </div>

    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4 mt-5">
        <div class="panel-card p-5">
            <div class="flex items-center gap-3">
                <span class="w-9 h-9 bg-[#0C3B2E] text-white flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 shrink-0" fill="currentColor" viewBox="0 0 24 24"><path fill-rule="evenodd" d="M11.097 1.515a.75.75 0 011.06 0l4.5 4.5a.75.75 0 010 1.06l-6.22 6.22a2.25 2.25 0 01-1.06.606l-3.725.996a.75.75 0 01-.928-.928l.996-3.725a2.25 2.25 0 01.606-1.06l6.22-6.22a.75.75 0 010-1.06l-4.5-4.5a.75.75 0 010-1.06z" clip-rule="evenodd"/></svg>
                </span>
                <h3 class="font-semibold text-slate-900 dark:text-white">Write for real readers</h3>
            </div>
            <p class="text-sm text-slate-600 dark:text-slate-400 mt-3 leading-relaxed">Use your own words and experience. The final post must sound like a person wrote it. Generic filler and repetitive phrasing will be returned for a rewrite.</p>
        </div>

        <div class="panel-card p-5">
            <div class="flex items-center gap-3">
                <span class="w-9 h-9 bg-[#0C3B2E] text-white flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 shrink-0" fill="currentColor" viewBox="0 0 24 24"><path d="M12 15a3 3 0 100-6 3 3 0 000 6z"/><path fill-rule="evenodd" d="M1.323 11.447C2.811 6.976 7.028 3.75 12 3.75c4.97 0 9.19 3.223 10.677 7.69.12.362.12.752 0 1.113-1.487 4.471-5.705 7.697-10.677 7.697-4.97 0-9.19-3.223-10.677-7.69a1.762 1.762 0 010-1.113zM17.25 12a5.25 5.25 0 11-10.5 0 5.25 5.25 0 0110.5 0z" clip-rule="evenodd"/></svg>
                </span>
                <h3 class="font-semibold text-slate-900 dark:text-white">Keep it focused</h3>
            </div>
            <p class="text-sm text-slate-600 dark:text-slate-400 mt-3 leading-relaxed">Make one clear point and support it. A concise post with real value outperforms a long one padded with repeated ideas.</p>
        </div>

        <div class="panel-card p-5">
            <div class="flex items-center gap-3">
                <span class="w-9 h-9 bg-[#0C3B2E] text-white flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                </span>
                <h3 class="font-semibold text-slate-900 dark:text-white">Be useful</h3>
            </div>
            <p class="text-sm text-slate-600 dark:text-slate-400 mt-3 leading-relaxed">Answer a question the reader actually has. Share concrete steps, picks or examples rather than vague overviews.</p>
        </div>

        <div class="panel-card p-5">
            <div class="flex items-center gap-3">
                <span class="w-9 h-9 bg-[#0C3B2E] text-white flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25"/><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v.01"/></svg>
                </span>
                <h3 class="font-semibold text-slate-900 dark:text-white">Support what you say</h3>
            </div>
            <p class="text-sm text-slate-600 dark:text-slate-400 mt-3 leading-relaxed">Cite sources where relevant and write from genuine knowledge. Do not present uncertain claims as fact.</p>
        </div>

        <div class="panel-card p-5">
            <div class="flex items-center gap-3">
                <span class="w-9 h-9 bg-[#0C3B2E] text-white flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/><path stroke-linecap="round" stroke-linejoin="round" d="M9 14.25l2 2 4-4"/></svg>
                </span>
                <h3 class="font-semibold text-slate-900 dark:text-white">Publish original work</h3>
            </div>
            <p class="text-sm text-slate-600 dark:text-slate-400 mt-3 leading-relaxed">All content must be yours. Copied text, repackaged press releases or keyword filled writing leads to permanent removal.</p>
        </div>

        <div class="panel-card p-5">
            <div class="flex items-center gap-3">
                <span class="w-9 h-9 bg-[#0C3B2E] text-white flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 shrink-0" fill="currentColor" viewBox="0 0 24 24"><path d="M7.5 6v.75H5.513c-.96 0-1.764.724-1.865 1.679l-1.32 12.38A1.5 1.5 0 003.81 22.5h16.38A1.5 1.5 0 001.49 20.809l-1.32-12.38c-.101-.955-.905-1.679-1.865-1.679H16.5v-.75A2.25 2.25 0 0014.25 3.75h-4.5A2.25 2.25 0 007.5 6z"/><path fill-rule="evenodd" d="M9 9.75A1.5 1.5 0 0110.5 8.25h3A1.5 1.5 0 0115 9.75v.75H9v-.75zm1.5 3a.75.75 0 01.75-.75h3a.75.75 0 010 1.5h-3a.75.75 0 01-.75-.75zm0 3a.75.75 0 01.75-.75h3a.75.75 0 010 1.5h-3a.75.75 0 01-.75-.75z" clip-rule="evenodd"/></svg>
                </span>
                <h3 class="font-semibold text-slate-900 dark:text-white">Complete the extras</h3>
            </div>
            <p class="text-sm text-slate-600 dark:text-slate-400 mt-3 leading-relaxed">Add at least one FAQ and fill in the meta title and description. This helps readers and search engines understand your topic.</p>
        </div>

        <div class="panel-card p-5">
            <div class="flex items-center gap-3">
                <span class="w-9 h-9 bg-[#0C3B2E] text-white flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path stroke-linecap="round" stroke-linejoin="round" d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/></svg>
                </span>
                <h3 class="font-semibold text-slate-900 dark:text-white">Affiliate links</h3>
            </div>
            <p class="text-sm text-slate-600 dark:text-slate-400 mt-3 leading-relaxed">When a post includes affiliate links, turn on the affiliate toggle before submitting. The required disclosure is then added for you.</p>
        </div>

        <div class="panel-card p-5">
            <div class="flex items-center gap-3">
                <span class="w-9 h-9 bg-[#0C3B2E] text-white flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                </span>
                <h3 class="font-semibold text-slate-900 dark:text-white">Submission pace</h3>
            </div>
            <p class="text-sm text-slate-600 dark:text-slate-400 mt-3 leading-relaxed">You can submit one post for review every 24 hours. Drafts are unlimited. Use the time to refine your next draft.</p>
        </div>

        <div class="panel-card p-5">
            <div class="flex items-center gap-3">
                <span class="w-9 h-9 bg-[#0C3B2E] text-white flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5.586a1 1 0 0 1 .707.293l5.414 5.414a1 1 0 0 1 .293.707V19a2 2 0 0 1-2 2Z"/></svg>
                </span>
                <h3 class="font-semibold text-slate-900 dark:text-white">How review works</h3>
            </div>
            <p class="text-sm text-slate-600 dark:text-slate-400 mt-3 leading-relaxed">Submitted posts queue for admin review. An admin may approve, edit or return a post with a note. Address the note and resubmit. Published posts are then locked for further edits.</p>
        </div>
    </div>

    <div class="border border-red-200 dark:border-red-500/30 bg-white dark:bg-slate-900 p-5 mt-4">
        <div class="flex items-center gap-3">
            <span class="w-9 h-9 bg-red-50 dark:bg-red-500/10 text-red-600 dark:text-red-300 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5 shrink-0" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 14h-2v-2h2v2zm0-4h-2V7h2v5z"/></svg>
            </span>
            <h3 class="font-semibold text-slate-900 dark:text-white">External link policy</h3>
        </div>
        <ul class="mt-4 space-y-2.5 text-sm text-slate-600 dark:text-slate-400 leading-relaxed">
            <li class="flex gap-2.5"><span class="w-5 h-5 bg-red-50 dark:bg-red-500/10 text-red-500 flex items-center justify-center shrink-0 mt-0.5"><svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg></span>Never link to adult, gambling, betting, drugs, hacking, piracy or any unsafe destination.</li>
            <li class="flex gap-2.5"><span class="w-5 h-5 bg-red-50 dark:bg-red-500/10 text-red-500 flex items-center justify-center shrink-0 mt-0.5"><svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg></span>Never link to unfamiliar, low quality or questionable sites for the sake of a commission.</li>
            <li class="flex gap-2.5"><span class="w-5 h-5 bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600 flex items-center justify-center shrink-0 mt-0.5"><svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg></span>Affiliate links are welcome only from reputable sources that readers already trust, such as major retailers and well known brands.</li>
        </ul>
        <p class="text-sm font-semibold text-red-600 dark:text-red-400 mt-4">A post that breaks the link policy is rejected and the account may be removed.</p>
    </div>

    <div class="border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900/60 p-5 mt-4 text-sm text-slate-600 dark:text-slate-400 leading-relaxed">
        <span class="font-semibold text-slate-900 dark:text-white">About your account</span>
        You can delete it at any time from your <a href="{{ route('author.profile.edit') }}" class="text-emerald-700 dark:text-emerald-300 hover:underline font-semibold">Profile</a> page. Drafts and returned posts are removed with it, while published posts stay online under a former author name.
    </div>

    <div class="mt-6 flex flex-wrap items-center gap-3">
        <a href="{{ route('author.posts.create') }}" class="inline-flex items-center gap-2 h-11 px-6 rounded-lg bg-[#0C3B2E] hover:bg-[#072A20] text-white font-semibold text-sm transition">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Z"/></svg>
            Start writing
        </a>
        <a href="{{ route('author.dashboard') }}" class="inline-flex items-center gap-2 h-11 px-6 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800 font-semibold text-sm transition">
            Back to dashboard
        </a>
    </div>
</div>
@endsection

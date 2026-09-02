@extends('frontend.author-dashboard.layout')

@section('title', 'Posting Rules')

@section('content')
<div class="w-full">
    <div class="panel-card p-6 sm:p-8">
        <div class="flex items-start gap-4">
            <div class="icon-tile w-12 h-12 shrink-0">
                <svg class="w-6 h-6 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5.586a1 1 0 0 1 .707.293l5.414 5.414a1 1 0 0 1 .293.707V19a2 2 0 0 1-2 2Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M9 5a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v2a2 2 0 0 1-2 2H9a2 2 0 0 1-2-2V5Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M12 12h.01M12 16h.01"/></svg>
            </div>
            <div>
                <h2 class="text-xl font-bold text-slate-900 dark:text-white">Posting Rules</h2>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1 leading-relaxed">Every post goes through admin review before it is published. When a rule is broken, the post is returned with a note explaining what to fix, or it is removed.</p>
            </div>
        </div>
    </div>

    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4 mt-5">
        <div class="panel-card p-5">
            <div class="flex items-center gap-3">
                <span class="icon-tile w-9 h-9 shrink-0">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487 18.549 2.8a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10"/></svg>
                </span>
                <h3 class="font-semibold text-slate-900 dark:text-white">Write for real readers</h3>
            </div>
            <p class="text-sm text-slate-600 dark:text-slate-400 mt-3 leading-relaxed">Write in your own words, from your own knowledge and experience. Generic filler, thin rewrites of other articles and obviously machine generated text are returned for a rewrite.</p>
        </div>

        <div class="panel-card p-5">
            <div class="flex items-center gap-3">
                <span class="icon-tile w-9 h-9 shrink-0">
                    <svg class="w-5 h-5 shrink-0" fill="currentColor" viewBox="0 0 24 24"><path d="M12 15a3 3 0 100-6 3 3 0 000 6z"/><path fill-rule="evenodd" d="M1.323 11.447C2.811 6.976 7.028 3.75 12 3.75c4.97 0 9.19 3.223 10.677 7.69.12.362.12.752 0 1.113-1.487 4.471-5.705 7.697-10.677 7.697-4.97 0-9.19-3.223-10.677-7.69a1.762 1.762 0 010-1.113zM17.25 12a5.25 5.25 0 11-10.5 0 5.25 5.25 0 0110.5 0z" clip-rule="evenodd"/></svg>
                </span>
                <h3 class="font-semibold text-slate-900 dark:text-white">Keep it focused</h3>
            </div>
            <p class="text-sm text-slate-600 dark:text-slate-400 mt-3 leading-relaxed">Make one clear point and support it well. A concise post with real value is easier to read and easier to trust than a long post padded with repeated ideas.</p>
        </div>

        <div class="panel-card p-5">
            <div class="flex items-center gap-3">
                <span class="icon-tile w-9 h-9 shrink-0">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                </span>
                <h3 class="font-semibold text-slate-900 dark:text-white">Be useful</h3>
            </div>
            <p class="text-sm text-slate-600 dark:text-slate-400 mt-3 leading-relaxed">Answer a question the reader actually has. Concrete steps, honest recommendations, real examples and numbers help far more than vague overviews.</p>
        </div>

        <div class="panel-card p-5">
            <div class="flex items-center gap-3">
                <span class="icon-tile w-9 h-9 shrink-0">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25"/><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v.01"/></svg>
                </span>
                <h3 class="font-semibold text-slate-900 dark:text-white">Support what you say</h3>
            </div>
            <p class="text-sm text-slate-600 dark:text-slate-400 mt-3 leading-relaxed">Write from genuine knowledge and link a source where it helps the reader verify the point. If you are not certain about something, say so instead of presenting it as fact.</p>
        </div>

        <div class="panel-card p-5">
            <div class="flex items-center gap-3">
                <span class="icon-tile w-9 h-9 shrink-0">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/><path stroke-linecap="round" stroke-linejoin="round" d="M9 14.25l2 2 4-4"/></svg>
                </span>
                <h3 class="font-semibold text-slate-900 dark:text-white">Publish original work</h3>
            </div>
            <p class="text-sm text-slate-600 dark:text-slate-400 mt-3 leading-relaxed">Everything you submit must be your own writing. Copied text, repackaged press releases and keyword stuffed articles are removed, and repeated cases end the account.</p>
        </div>

        <div class="panel-card p-5">
            <div class="flex items-center gap-3">
                <span class="icon-tile w-9 h-9 shrink-0">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25ZM6.75 12h.008v.008H6.75V12Zm0 3h.008v.008H6.75V15Zm0 3h.008v.008H6.75V18Z"/></svg>
                </span>
                <h3 class="font-semibold text-slate-900 dark:text-white">Complete the extras</h3>
            </div>
            <p class="text-sm text-slate-600 dark:text-slate-400 mt-3 leading-relaxed">Add at least one FAQ and fill in the meta title and description before you submit. These fields help readers and search engines understand exactly what your post covers.</p>
        </div>

        <div class="panel-card p-5">
            <div class="flex items-center gap-3">
                <span class="icon-tile w-9 h-9 shrink-0">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path stroke-linecap="round" stroke-linejoin="round" d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/></svg>
                </span>
                <h3 class="font-semibold text-slate-900 dark:text-white">Affiliate links</h3>
            </div>
            <p class="text-sm text-slate-600 dark:text-slate-400 mt-3 leading-relaxed">When a post includes affiliate links, turn on the affiliate toggle before you submit. The required disclosure is added for you, which keeps the post compliant and trustworthy.</p>
        </div>

        <div class="panel-card p-5">
            <div class="flex items-center gap-3">
                <span class="icon-tile w-9 h-9 shrink-0">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                </span>
                <h3 class="font-semibold text-slate-900 dark:text-white">Submission pace</h3>
            </div>
            <p class="text-sm text-slate-600 dark:text-slate-400 mt-3 leading-relaxed">You can submit one post for review every 24 hours. Drafts are unlimited, so use the waiting time to improve your next one.</p>
        </div>

        <div class="panel-card p-5">
            <div class="flex items-center gap-3">
                <span class="icon-tile w-9 h-9 shrink-0">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5.586a1 1 0 0 1 .707.293l5.414 5.414a1 1 0 0 1 .293.707V19a2 2 0 0 1-2 2Z"/></svg>
                </span>
                <h3 class="font-semibold text-slate-900 dark:text-white">How review works</h3>
            </div>
            <p class="text-sm text-slate-600 dark:text-slate-400 mt-3 leading-relaxed">Admins approve, edit or return a post with a note. Fix what the note asks for and submit it again. Published posts are locked and can only be changed by an admin.</p>
        </div>
    </div>

    <div class="border border-amber-200 dark:border-amber-500/30 bg-white dark:bg-slate-900 p-5 mt-4 rounded-xl">
        <div class="flex items-center gap-3">
            <span class="w-9 h-9 bg-amber-50 dark:bg-amber-400/10 text-amber-600 dark:text-amber-300 flex items-center justify-center shrink-0 rounded-lg">
                <svg class="w-5 h-5 shrink-0" fill="currentColor" viewBox="0 0 24 24"><path fill-rule="evenodd" d="M9.401 3.003c1.155-2 4.043-2 5.197 0l7.355 12.748c1.154 2-.29 4.5-2.599 4.5H4.645c-2.309 0-3.752-2.5-2.598-4.5L9.4 3.003ZM12 8.25a.75.75 0 0 1 .75.75v3.75a.75.75 0 0 1-1.5 0V9a.75.75 0 0 1 .75-.75Zm0 8.25a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Z" clip-rule="evenodd"/></svg>
            </span>
            <h3 class="font-semibold text-slate-900 dark:text-white">External link policy</h3>
        </div>
        <ul class="mt-4 space-y-2.5 text-sm text-slate-600 dark:text-slate-400 leading-relaxed">
            <li class="flex gap-2.5"><span class="w-5 h-5 bg-amber-50 dark:bg-amber-400/10 text-amber-600 dark:text-amber-300 flex items-center justify-center shrink-0 mt-0.5 rounded-md"><svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg></span>Never link to adult, gambling, betting, drugs, hacking, piracy or any unsafe destination.</li>
            <li class="flex gap-2.5"><span class="w-5 h-5 bg-amber-50 dark:bg-amber-400/10 text-amber-600 dark:text-amber-300 flex items-center justify-center shrink-0 mt-0.5 rounded-md"><svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg></span>Never link to unfamiliar, low quality or questionable sites for the sake of a commission.</li>
            <li class="flex gap-2.5"><span class="w-5 h-5 bg-[#F0F7F3] dark:bg-[#2E7856]/10 text-[#27654A] flex items-center justify-center shrink-0 mt-0.5 rounded-md"><svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg></span>Affiliate links are welcome only from reputable sources that readers already trust, such as major retailers and well known brands.</li>
        </ul>
        <p class="text-sm font-semibold text-amber-800 dark:text-amber-300 mt-4">A post that breaks the link policy is rejected and the account may be removed.</p>
    </div>

    <div class="border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900/60 p-5 mt-4 text-sm text-slate-600 dark:text-slate-400 leading-relaxed rounded-xl">
        <span class="block font-semibold text-slate-900 dark:text-white mb-1">About your account</span>
        Delete it anytime from your <a href="{{ route('author.profile.edit') }}" class="text-[#1F513A] dark:text-[#6FB393] hover:underline font-semibold">Profile</a>. Drafts go with it; published posts stay online.
    </div>

    <div class="mt-6 flex flex-wrap items-center gap-3">
        <a href="{{ route('author.posts.create') }}" class="inline-flex items-center gap-2 h-11 px-6 rounded-lg bg-[#2E7856] hover:bg-[#27654A] text-white font-semibold text-sm transition">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Z"/></svg>
            Start writing
        </a>
        <a href="{{ route('author.dashboard') }}" class="inline-flex items-center gap-2 h-11 px-6 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800 font-semibold text-sm transition">
            Back to dashboard
        </a>
    </div>
</div>
@endsection

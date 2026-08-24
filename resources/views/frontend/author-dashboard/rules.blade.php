@extends('frontend.author-dashboard.layout')

@section('title', 'Posting rules')

@section('content')
<div class="max-w-[760px] prose prose-slate dark:prose-invert">
    <h2 class="text-xl font-bold text-slate-900 dark:text-white mb-1">Before you publish — read this</h2>
    <p class="text-sm text-slate-500 mb-5">These rules apply to every post you submit. Breaking them can get a post returned, your account suspended, or your account permanently deleted.</p>

    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-6 space-y-5 text-sm text-slate-700 dark:text-slate-300 leading-relaxed">
        <div>
            <h3 class="font-bold text-slate-900 dark:text-white text-base mb-1.5">1. No AI slop</h3>
            <p>AI-assisted drafts are allowed (grammar, structure) but the final post must read like a human wrote it. No "In conclusion, it's important to note that…", no listicles with three near-identical bullets, no phrases that sound like a chatbot. If a reviewer thinks it's AI-generated and unreadable, the post will be returned.</p>
        </div>
        <div>
            <h3 class="font-bold text-slate-900 dark:text-white text-base mb-1.5">2. No fluff or repetition</h3>
            <p>Say it once, say it well. Don't pad a 400-word post into 1200 words by restating the intro three different ways. Reviewers trim redundancy and return posts that read as filler.</p>
        </div>
        <div>
            <h3 class="font-bold text-slate-900 dark:text-white text-base mb-1.5">3. Be genuinely helpful</h3>
            <p>Every post must solve a real reader problem. "How to choose a phone under $300" with concrete picks, prices, and trade-offs is helpful. "Phones are great and there are many options" is not.</p>
        </div>
        <div>
            <h3 class="font-bold text-slate-900 dark:text-white text-base mb-1.5">4. E-E-A-T — experience, expertise, authoritativeness, trust</h3>
            <p>Back up claims with sources. If you recommend a product, say why <em>you</em> would pick it. Disclose your experience with the topic. Don't impersonate experts in fields you don't know.</p>
        </div>
        <div>
            <h3 class="font-bold text-slate-900 dark:text-white text-base mb-1.5">5. Disclose affiliate links</h3>
            <p>If your post contains any affiliate link (Amazon, Impact, ShareASale, etc.) toggle the "This post contains affiliate links" switch before submitting. A disclosure box will appear at the top of the published post — required by FTC and EU law.</p>
        </div>
        <div>
            <h3 class="font-bold text-slate-900 dark:text-white text-base mb-1.5">6. One submission per 24 hours</h3>
            <p>You can submit one post for admin review per 24 hours. Drafts can be saved unlimited times — only "Submit for review" triggers the limit. Use the time to polish, not to spam.</p>
        </div>
        <div>
            <h3 class="font-bold text-slate-900 dark:text-white text-base mb-1.5">7. No spam, no plagiarism, no stolen content</h3>
            <p>Don't copy-paste from other sites. Don't submit press releases or sponsored posts disguised as articles. Don't stuff keywords. Posts must be your own work. Plagiarism = permanent account deletion, no warning.</p>
        </div>
        <div>
            <h3 class="font-bold text-slate-900 dark:text-white text-base mb-1.5">8. Admin has final say</h3>
            <p>Submitted posts go to an admin queue. The admin may approve as-is, edit it, or return it with a note. You'll see the note on the post's row. Edit, fix the issues, re-submit. The cycle continues until the post is approved and published — then it's locked from your edits.</p>
        </div>
        <div>
            <h3 class="font-bold text-slate-900 dark:text-white text-base mb-1.5">9. Account deletion</h3>
            <p>You can delete your account at any time from <a href="{{ route('author.profile.edit') }}" class="text-[#0C3B2E] dark:text-emerald-300 hover:underline">Profile</a>. Drafts and returned posts are deleted; your published posts stay on the site but are reassigned to "Former author".</p>
        </div>
    </div>

    <div class="mt-6">
        <a href="{{ route('author.posts.create') }}" class="inline-flex items-center h-11 px-5 bg-[#0C3B2E] hover:bg-[#072A20] text-white font-semibold text-sm">Got it — let me write</a>
    </div>
</div>
@endsection

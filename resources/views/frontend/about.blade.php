@extends('layouts.app')
@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 py-10">
    <div class="card-elev p-6 sm:p-10">
        <h1 class="font-extrabold text-3xl sm:text-4xl text-slate-900 dark:text-white tracking-tight">About Huvanti</h1>
        <p class="text-slate-500 dark:text-slate-400 mt-2">huvanti.com <span class="w-1 h-1 bg-slate-300 dark:bg-slate-600 inline-block mx-1 align-middle"></span> Explore Ideas. Inspire Life.</p>
        <div class="prose dark:prose-invert max-w-none mt-8">
            @if($page)
                {!! $page->content !!}
            @else
                <p>Huvanti is a multi niche publishing platform built for curious minds who want more from their reading time. We bring together technology, health and wellness, finance, travel, lifestyle and education, not as scattered topics, but as connected parts of a well lived life. Our goal is simple: publish articles that are genuinely useful, easy to read and honest about what we know and what we do not.</p>
                <p>Every piece on Huvanti starts with a question real people ask. Should you switch to a high protein diet on a budget? How does spaced repetition actually work? What do first time investors need to understand before opening an account? We research each answer carefully, test claims where we can, and write in plain language that respects your intelligence and your time.</p>
                <h2>What we cover</h2>
                <p>Our six core categories are curated by editors who read widely in their fields. Technology explains the tools and trends shaping daily life. Health and Wellness offers practical, evidence informed guidance on food, sleep, fitness and mental focus. Finance breaks down investing, saving and money habits into steps anyone can follow. Travel covers meaningful destinations and smarter trip planning. Lifestyle explores calm, intentional living, and Education shares learning techniques that actually work.</p>
                <h2>How we work</h2>
                <p>Articles are drafted, reviewed and fact checked before publication. When we cite studies or data, we link to the source so you can verify it yourself. When something changes, whether a product update, a new guideline or a correction from a reader, we update the article and note the change. Reader feedback is part of our editorial process, and every comment is read by a human being.</p>
                <h2>Our mission</h2>
                <p>To explore ideas that matter and inspire life through clear, human centered content. We measure success not by clicks, but by whether a reader finishes an article knowing something useful they did not know before.</p>
                <h2>Meet the team</h2>
                <p>Huvanti is written and edited by a small independent team led by <strong>Pritam Sarkar</strong>, working with contributors who care deeply about their subjects. We are readers first and publishers second, and we build Huvanti around the experience we want for ourselves: calm pages, honest writing and zero clutter.</p>
            @endif
        </div>
        <div class="mt-8 grid sm:grid-cols-3 gap-4">
            <div class="bg-emerald-50/70 dark:bg-[#2a2a2a] border border-emerald-100 dark:border-[#383838] p-5"><div class="font-bold text-2xl text-[#0C3B2E] dark:text-emerald-300">6</div><div class="text-sm font-semibold text-slate-900 dark:text-white mt-1">Curated niches</div><div class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">Edited, not aggregated</div></div>
            <div class="bg-emerald-50/70 dark:bg-[#2a2a2a] border border-emerald-100 dark:border-[#383838] p-5"><div class="font-bold text-2xl text-[#0C3B2E] dark:text-emerald-300">Weekly</div><div class="text-sm font-semibold text-slate-900 dark:text-white mt-1">New articles</div><div class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">Fresh, useful, reviewed</div></div>
            <div class="bg-emerald-50/70 dark:bg-[#2a2a2a] border border-emerald-100 dark:border-[#383838] p-5"><div class="font-bold text-2xl text-[#0C3B2E] dark:text-emerald-300">12k+</div><div class="text-sm font-semibold text-slate-900 dark:text-white mt-1">Monthly readers</div><div class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">And growing steadily</div></div>
        </div>
    </div>
</div>
@endsection

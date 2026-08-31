@extends('layouts.app')
@php
    $metaTitle = ($page->meta_title ?? null) ?: 'About ' . setting('site_name','huvanti.com');
    $metaDescription = ($page->meta_description ?? null) ?: ('Learn about ' . setting('site_name','huvanti.com') . ', our editorial mission and the team behind the articles.');
@endphp
@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 py-10">
    <div class="page-head !pt-2 !pb-0">
        <nav class="flex items-center gap-1.5 text-[13px] text-slate-400 dark:text-slate-500 mb-2.5" aria-label="Breadcrumb">
            <a href="/" class="hover:text-[#2E7856] dark:hover:text-[#6FB393] transition">Home</a>
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m9 6 6 6-6 6"/></svg>
            <span class="text-slate-700 dark:text-slate-300 font-medium">About</span>
        </nav>
        <h1>About Huvanti</h1>
        <p class="lede">Explore Ideas. Inspire Life.</p>
    </div>
    <div class="card-elev p-6 sm:p-9 mt-6">
        <div class="prose dark:prose-invert max-w-none prose-head:tracking-tight">
            @if($page)
                {!! $page->content !!}
            @else
                <h2>What Huvanti is</h2>
                <p>Huvanti is an independent online publication. We write practical, easy to read articles across technology, health and wellness, finance, travel, lifestyle and education. One topic at a time, explained in plain language.</p>
                <h2>Why we started</h2>
                <p>Most of what we read online was either too complicated, too long or written for search engines instead of people. Huvanti exists to fix that. Every article answers a real question, gets straight to the point and respects your time.</p>
                <h2>What we cover</h2>
                <p>Technology explains the tools and trends that shape daily life. Health and Wellness covers food, sleep, fitness and focus with practical guidance. Finance breaks down saving, investing and money habits into steps you can act on. Travel shares meaningful destinations and smarter planning. Lifestyle explores calm and intentional living. Education explains learning methods that help knowledge stick.</p>
                <h2>How our content is made</h2>
                <p>Each article starts with a question a real person would ask. We research the answer, check claims against original sources and review every piece before it goes live. When we cite a study or statistic, we link to it so you can verify it yourself. When information changes, we update the article and note the change.</p>
                <h2>Our promise</h2>
                <p>No clickbait. No filler. No fake urgency. If we recommend something, it is because we believe it helps you, not because someone paid us to say so. Where affiliate links appear, they are disclosed clearly on the page.</p>
                <h2>Talk to us</h2>
                <p>Questions, corrections and ideas are always welcome. Reach us any time through the contact page.</p>
            @endif
        </div>
    </div>
</div>
@endsection

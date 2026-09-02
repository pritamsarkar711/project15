@extends('layouts.app')

@php
    $metaTitle = ($page->meta_title ?? null) ?: 'About Huvanti: Our Story, Mission and Editorial Team';
    $metaDescription = ($page->meta_description ?? null) ?: 'Learn about Huvanti, our editorial mission, content standards, and the team behind our independent and reader first publishing.';
@endphp

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 py-10">
    <div class="page-head !pt-2 !pb-0">
        <nav class="flex items-center gap-1.5 text-[13px] text-slate-400 dark:text-slate-500 mb-2.5" aria-label="Breadcrumb">
            <a href="/" class="hover:text-[var(--brand)] dark:hover:text-[var(--brand-light)] transition">Home</a>
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m9 6 6 6-6 6"/></svg>
            <span class="text-slate-700 dark:text-slate-300 font-medium">About</span>
        </nav>
        <h1>About Huvanti</h1>
        <p class="lede">Explore Ideas and Inspire Life</p>
    </div>

    <div class="card-elev p-6 sm:p-9 mt-6">
        <div class="prose dark:prose-invert max-w-none prose-head:tracking-tight">
            @if($page && !empty(trim($page->content)))
                {!! $page->content !!}
            @else
                <h2>About Huvanti</h2>
                <p>Huvanti is an independent digital publication committed to delivering clear, insightful, and practical articles across technology, health and wellness, finance, travel, lifestyle, and education. We believe knowledge should be accessible, engaging, and genuinely useful in daily life.</p>

                <h3>Our Mission</h3>
                <p>Our mission is to help curious minds explore ideas that matter and inspire positive everyday choices. The modern internet is filled with overwhelming jargon and shallow clickbait. We built Huvanti to offer a thoughtful alternative where readers can find thoroughly researched guides written with clarity and care.</p>

                <h3>What We Cover</h3>
                <p>Technology: We explore modern tools, emerging digital innovations, and software trends, breaking down complex developments into practical insights for everyday creators and professionals.</p>
                <p>Health and Wellness: We share research backed guides on balanced nutrition, restorative sleep, physical fitness, and mental well being to help you build sustainable daily habits.</p>
                <p>Finance: We provide straightforward advice on personal budgeting, saving strategies, mindful spending, and wealth building fundamentals designed for beginners and experienced planners alike.</p>
                <p>Travel: We highlight authentic destinations, cultural journeys, and mindful travel tips to help you experience the world with curiosity and respect.</p>
                <p>Lifestyle: We share ideas on intentional living, calm routines, organization, and creative hobbies that bring balance to your personal space and schedule.</p>
                <p>Education: We explore evidence based study techniques, cognitive tools, and lifelong learning methods that help you master new skills with confidence.</p>

                <h3>Our Editorial Standards</h3>
                <p>Every article published on Huvanti is conceptualized, written, and verified by real human writers and subject enthusiasts. We verify facts against primary documentation, scientific studies, and reputable official sources. We do not publish unverified or automated text. When new developments emerge, we review and refresh our content to ensure ongoing accuracy.</p>

                <h3>Independence and Integrity</h3>
                <p>Editorial integrity is the cornerstone of our publication. Our reviews, ratings, and recommendations are guided solely by independent research and the genuine interests of our readers. Commercial sponsorships or affiliate relationships never compromise our editorial perspective or honest evaluations.</p>

                <h3>Get in Touch</h3>
                <p>We value open conversation with our community. If you have questions, feedback, or suggestions for upcoming topics, we invite you to connect with us through our dedicated contact page.</p>
            @endif
        </div>
    </div>
</div>
@endsection

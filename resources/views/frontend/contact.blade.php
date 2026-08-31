@extends('layouts.app')
@php
    $metaTitle = 'Contact Huvanti — Questions, Feedback & Support';
    $metaDescription = 'Get in touch with the ' . setting('site_name','huvanti.com') . ' team - questions, feedback, corrections, partnerships or press inquiries.';
@endphp
@section('content')
<div class="max-w-6xl mx-auto px-4 sm:px-6 py-10">
    <div class="page-head !pt-2 !pb-0">
        <nav class="flex items-center gap-1.5 text-[13px] text-slate-400 dark:text-slate-500 mb-2.5" aria-label="Breadcrumb">
            <a href="/" class="hover:text-[#2E7856] dark:hover:text-[#6FB393] transition">Home</a>
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m9 6 6 6-6 6"/></svg>
            <span class="text-slate-700 dark:text-slate-300 font-medium">Contact</span>
        </nav>
        <h1>Contact Huvanti</h1>
        <p class="lede">We would love to hear from you. Whether you have a question about an article, feedback on the site, a partnership idea, a correction to report or press inquiry, choose a reason below and we will get back within 24 hours on working days.</p>
    </div>
    <div class="grid lg:grid-cols-12 gap-6 mt-4">
        <div class="lg:col-span-5 space-y-3">
            <div class="flex items-center gap-3.5 card-elev p-4">
                <span class="icon-tile w-10 h-10"><svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m22 7-8.5 5.5a4 4 0 0 1-3 0L2 7"/><rect width="20" height="14" x="2" y="5" rx="2"/></svg></span>
                <span class="min-w-0"><span class="block text-sm font-semibold text-slate-900 dark:text-white truncate">huvantiofficial@gmail.com</span><span class="block text-[13px] text-slate-500 dark:text-slate-400">All inquiries — feedback, pitches &amp; corrections</span></span>
            </div>
            <div class="flex items-center gap-3.5 card-elev p-4">
                <span class="icon-tile w-10 h-10"><svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l4 2"/></svg></span>
                <span class="min-w-0"><span class="block text-sm font-semibold text-slate-900 dark:text-white">Replies within 24 hours</span><span class="block text-[13px] text-slate-500 dark:text-slate-400">Monday to Saturday</span></span>
            </div>
            <div class="card-elev p-4">
                <span class="block text-[13px] font-bold text-slate-900 dark:text-white mb-1.5 tracking-tight">Before you write</span>
                <span class="block text-[13px] text-slate-500 dark:text-slate-400 leading-relaxed">Corrections move fastest — include the article link and the exact sentence. Pitches are reviewed every Friday.</span>
            </div>
        </div>
        <div class="lg:col-span-7">
            <div class="card-elev p-6 sm:p-8">
                <h2 class="text-[15px] font-bold text-slate-900 dark:text-white tracking-tight mb-5">Send a message</h2>
                @if(session('success'))<div class="bg-[#F0F7F3] dark:bg-[#57A37E]/10 border border-[#C7E0D4] dark:border-[#57A37E]/20 text-[#173A2A] dark:text-[#6FB393] px-4 py-3 text-sm mb-4 rounded-lg">{{ session('success') }}</div>@endif
                <form action="{{ route('contact.store') }}" method="POST" class="space-y-4">
                    @csrf
                    <div class="grid sm:grid-cols-2 gap-4">
                        <div>
                            <label class="label">Name *</label>
                            <input type="text" name="name" required value="{{ old('name') }}" class="input">
                            @error('name')<p class="text-xs text-red-600 dark:text-red-400 mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="label">Email *</label>
                            <input type="email" name="email" required value="{{ old('email') }}" class="input">
                            @error('email')<p class="text-xs text-red-600 dark:text-red-400 mt-1">{{ $message }}</p>@enderror
                        </div>
                    </div>
                    <div>
                        <label class="label">Reason for contacting *</label>
                        <select name="reason" required class="input">
                            <option value="">Select a reason</option>
                            @foreach($reasons as $r)
                                <option value="{{ $r }}" @selected(old('reason')==$r)>{{ $r }}</option>
                            @endforeach
                        </select>
                        @error('reason')<p class="text-xs text-red-600 dark:text-red-400 mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="label">Subject</label>
                        <input type="text" name="subject" value="{{ old('subject') }}" placeholder="Optional" class="input">
                    </div>
                    <div>
                        <label class="label">Message *</label>
                        <textarea name="message" required rows="5" class="input" placeholder="How can we help?">{{ old('message') }}</textarea>
                        @error('message')<p class="text-xs text-red-600 dark:text-red-400 mt-1">{{ $message }}</p>@enderror
                    </div>
                    <button type="submit" class="btn btn-primary w-full !h-11">Send message</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

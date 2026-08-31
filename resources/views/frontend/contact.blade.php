@extends('layouts.app')
@php
    $metaTitle = 'Contact Huvanti — Questions, Feedback & Support';
    $metaDescription = 'Get in touch with the ' . setting('site_name','huvanti.com') . ' team - questions, feedback, corrections, partnerships or press inquiries.';
@endphp
@section('content')
<div class="max-w-6xl mx-auto px-4 sm:px-6 py-14">
    <div class="grid lg:grid-cols-12 gap-6">
        <div class="lg:col-span-5">
            <span class="kicker"><b>@</b> Say hello</span>
            <h1 class="mt-4 font-black text-[36px] sm:text-[46px] text-[#141A16] dark:text-[#F0F2EB] tracking-tight leading-[1.03]">Contact Huvanti<span class="text-[#F5C445]">.</span></h1>
            <p class="text-[15px] text-[#5C665E] dark:text-[#97A199] mt-4 leading-relaxed">We would love to hear from you. Whether you have a question about an article, feedback on the site, a partnership idea, a correction to report or press inquiry, choose a reason below and we will get back within 24 hours on working days.</p>
            <div class="mt-6 space-y-3 text-sm">
                <div class="flex items-center gap-3 bg-white dark:bg-[#141815] border border-[#E4E4DA] dark:border-[#262C28] p-4"><span class="chip w-10 h-10"><svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m22 7-8.5 5.5a4 4 0 0 1-3 0L2 7"/><rect width="20" height="14" x="2" y="5" rx="2"/></svg></span><span><span class="font-bold text-[14px] text-[#141A16] dark:text-[#F0F2EB]">huvantiofficial@gmail.com</span><br><span class="text-[12.5px] text-[#8B958C] dark:text-[#6B756C]">All inquiries — feedback, pitches &amp; corrections</span></span></div>
                <div class="flex items-center gap-3 bg-white dark:bg-[#141815] border border-[#E4E4DA] dark:border-[#262C28] p-4"><span class="chip w-10 h-10"><svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l4 2"/></svg></span><span><span class="font-bold text-[14px] text-[#141A16] dark:text-[#F0F2EB]">Replies within 24 hours</span><br><span class="text-[12.5px] text-[#8B958C] dark:text-[#6B756C]">Monday to Saturday</span></span></div>
            </div>
        </div>
        <div class="lg:col-span-7">
            <div class="card-elev p-6 sm:p-8">
                @if(session('success'))<div class="bg-emerald-50 dark:bg-emerald-400/10 border border-emerald-200 dark:border-emerald-400/20 text-emerald-800 dark:text-emerald-300 px-4 py-3 text-sm mb-4">{{ session('success') }}</div>@endif
                <form action="{{ route('contact.store') }}" method="POST" class="space-y-4">
                    @csrf
                    <div class="grid sm:grid-cols-2 gap-4">
                        <div>
                            <label class="text-[11px] font-extrabold uppercase tracking-[0.12em] text-[#141A16] dark:text-[#EDEFEA]">Name *</label>
                            <input type="text" name="name" required value="{{ old('name') }}" class="field mt-1.5 h-11 px-3 text-sm">
                            @error('name')<p class="text-xs text-red-600 dark:text-red-400 mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="text-[11px] font-extrabold uppercase tracking-[0.12em] text-[#141A16] dark:text-[#EDEFEA]">Email *</label>
                            <input type="email" name="email" required value="{{ old('email') }}" class="field mt-1.5 h-11 px-3 text-sm">
                            @error('email')<p class="text-xs text-red-600 dark:text-red-400 mt-1">{{ $message }}</p>@enderror
                        </div>
                    </div>
                    <div>
                        <label class="text-[11px] font-extrabold uppercase tracking-[0.12em] text-[#141A16] dark:text-[#EDEFEA]">Reason for contacting *</label>
                        <select name="reason" required class="field mt-1.5 h-11 px-3 text-sm">
                            <option value="">Select a reason</option>
                            @foreach($reasons as $r)
                                <option value="{{ $r }}" @selected(old('reason')==$r)>{{ $r }}</option>
                            @endforeach
                        </select>
                        @error('reason')<p class="text-xs text-red-600 dark:text-red-400 mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="text-[11px] font-extrabold uppercase tracking-[0.12em] text-[#141A16] dark:text-[#EDEFEA]">Subject</label>
                        <input type="text" name="subject" value="{{ old('subject') }}" placeholder="Optional" class="field mt-1.5 h-11 px-3 text-sm">
                    </div>
                    <div>
                        <label class="text-[11px] font-extrabold uppercase tracking-[0.12em] text-[#141A16] dark:text-[#EDEFEA]">Message *</label>
                        <textarea name="message" required rows="5" class="field mt-1.5 px-3 py-3 text-sm" placeholder="How can we help?">{{ old('message') }}</textarea>
                        @error('message')<p class="text-xs text-red-600 dark:text-red-400 mt-1">{{ $message }}</p>@enderror
                    </div>
                    <button type="submit" class="w-full h-12 bg-[#141A16] hover:bg-[#0C3B2E] text-white font-extrabold text-[12.5px] uppercase tracking-[0.14em] transition shadow-[4px_4px_0_0_#F5C445]">Send message</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

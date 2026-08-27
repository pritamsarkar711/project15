@extends('layouts.app')
@php
    $metaTitle = 'Contact ' . setting('site_name','huvanti.com');
    $metaDescription = 'Get in touch with the ' . setting('site_name','huvanti.com') . ' team - questions, feedback, corrections, partnerships or press inquiries.';
@endphp
@section('content')
<div class="max-w-6xl mx-auto px-4 sm:px-6 py-10">
    <div class="grid lg:grid-cols-12 gap-6">
        <div class="lg:col-span-5">
            <h1 class="font-extrabold text-3xl sm:text-4xl text-slate-900 dark:text-white tracking-tight">Contact Huvanti</h1>
            <p class="text-slate-600 dark:text-slate-400 mt-4 leading-relaxed">We would love to hear from you. Whether you have a question about an article, feedback on the site, a partnership idea, a correction to report or press inquiry, choose a reason below and we will get back within 24 hours on working days.</p>
            <div class="mt-6 space-y-3 text-sm">
                <div class="flex items-center gap-3 bg-white dark:bg-[#1e1e1e] border border-slate-200 dark:border-[#383838] p-3.5"><span class="w-10 h-10 bg-emerald-50 dark:bg-emerald-400/10 text-[#0C3B2E] dark:text-emerald-300 flex items-center justify-center shrink-0"><svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m22 7-8.5 5.5a4 4 0 0 1-3 0L2 7"/><rect width="20" height="14" x="2" y="5" rx="2"/></svg></span><span><span class="font-semibold text-slate-900 dark:text-white">hello@huvanti.com</span><br><span class="text-slate-500 dark:text-slate-400">General inquiries and feedback</span></span></div>
                <div class="flex items-center gap-3 bg-white dark:bg-[#1e1e1e] border border-slate-200 dark:border-[#383838] p-3.5"><span class="w-10 h-10 bg-emerald-50 dark:bg-emerald-400/10 text-[#0C3B2E] dark:text-emerald-300 flex items-center justify-center shrink-0"><svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H20v20H6.5a2.5 2.5 0 0 1 0-5H20"/></svg></span><span><span class="font-semibold text-slate-900 dark:text-white">editorial@huvanti.com</span><br><span class="text-slate-500 dark:text-slate-400">Pitches and corrections</span></span></div>
                <div class="flex items-center gap-3 bg-white dark:bg-[#1e1e1e] border border-slate-200 dark:border-[#383838] p-3.5"><span class="w-10 h-10 bg-emerald-50 dark:bg-emerald-400/10 text-[#0C3B2E] dark:text-emerald-300 flex items-center justify-center shrink-0"><svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l4 2"/></svg></span><span><span class="font-semibold text-slate-900 dark:text-white">Replies within 24 hours</span><br><span class="text-slate-500 dark:text-slate-400">Monday to Saturday</span></span></div>
            </div>
        </div>
        <div class="lg:col-span-7">
            <div class="card-elev p-6 sm:p-8">
                @if(session('success'))<div class="bg-emerald-50 dark:bg-emerald-400/10 border border-emerald-200 dark:border-emerald-400/20 text-emerald-800 dark:text-emerald-300 px-4 py-3 text-sm mb-4">{{ session('success') }}</div>@endif
                <form action="{{ route('contact.store') }}" method="POST" class="space-y-4">
                    @csrf
                    <div class="grid sm:grid-cols-2 gap-4">
                        <div>
                            <label class="text-sm font-medium text-slate-700 dark:text-slate-300">Name *</label>
                            <input type="text" name="name" required value="{{ old('name') }}" class="mt-1.5 w-full h-11 px-3 bg-slate-50 dark:bg-[#2a2a2a] border border-slate-200 dark:border-[#383838] text-slate-900 dark:text-white placeholder:text-slate-400 dark:placeholder:text-slate-500 focus:bg-white dark:focus:bg-slate-900 focus:border-emerald-300 dark:focus:border-emerald-500 focus:ring-4 focus:ring-emerald-100 dark:focus:ring-emerald-400/10 outline-none text-sm">
                            @error('name')<p class="text-xs text-red-600 dark:text-red-400 mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="text-sm font-medium text-slate-700 dark:text-slate-300">Email *</label>
                            <input type="email" name="email" required value="{{ old('email') }}" class="mt-1.5 w-full h-11 px-3 bg-slate-50 dark:bg-[#2a2a2a] border border-slate-200 dark:border-[#383838] text-slate-900 dark:text-white placeholder:text-slate-400 dark:placeholder:text-slate-500 focus:bg-white dark:focus:bg-slate-900 focus:border-emerald-300 dark:focus:border-emerald-500 focus:ring-4 focus:ring-emerald-100 dark:focus:ring-emerald-400/10 outline-none text-sm">
                            @error('email')<p class="text-xs text-red-600 dark:text-red-400 mt-1">{{ $message }}</p>@enderror
                        </div>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-slate-700 dark:text-slate-300">Reason for contacting *</label>
                        <select name="reason" required class="mt-1.5 w-full h-11 px-3 bg-slate-50 dark:bg-[#2a2a2a] border border-slate-200 dark:border-[#383838] text-slate-900 dark:text-white focus:bg-white dark:focus:bg-slate-900 focus:border-emerald-300 outline-none text-sm">
                            <option value="">Select a reason</option>
                            @foreach($reasons as $r)
                                <option value="{{ $r }}" @selected(old('reason')==$r)>{{ $r }}</option>
                            @endforeach
                        </select>
                        @error('reason')<p class="text-xs text-red-600 dark:text-red-400 mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="text-sm font-medium text-slate-700 dark:text-slate-300">Subject</label>
                        <input type="text" name="subject" value="{{ old('subject') }}" placeholder="Optional" class="mt-1.5 w-full h-11 px-3 bg-slate-50 dark:bg-[#2a2a2a] border border-slate-200 dark:border-[#383838] text-slate-900 dark:text-white placeholder:text-slate-400 dark:placeholder:text-slate-500 focus:bg-white dark:focus:bg-slate-900 focus:border-emerald-300 outline-none text-sm">
                    </div>
                    <div>
                        <label class="text-sm font-medium text-slate-700 dark:text-slate-300">Message *</label>
                        <textarea name="message" required rows="5" class="mt-1.5 w-full px-3 py-3 bg-slate-50 dark:bg-[#2a2a2a] border border-slate-200 dark:border-[#383838] text-slate-900 dark:text-white placeholder:text-slate-400 dark:placeholder:text-slate-500 focus:bg-white dark:focus:bg-slate-900 focus:border-emerald-300 outline-none text-sm" placeholder="How can we help?">{{ old('message') }}</textarea>
                        @error('message')<p class="text-xs text-red-600 dark:text-red-400 mt-1">{{ $message }}</p>@enderror
                    </div>
                    <button type="submit" class="w-full h-12 bg-[#0C3B2E] hover:bg-[#072A20] text-white font-semibold transition">Send message</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

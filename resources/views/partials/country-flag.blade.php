{{-- Country flag icon + name for a user (works with $user->country ISO code).
     Renders NOTHING when the user has not picked a country yet.
     Variables:
       $user — the user whose country is shown
       $class — (optional) Tailwind classes for the flag <img> (default w-4 h-3)
       $showName — (optional) when true, prints the country name next to the flag --}}
@php
    $flagCode = strtoupper(trim((string) ($user->country ?? '')));
    $flagUrl = \App\Support\Countries::flagUrl($flagCode);
    $flagName = \App\Support\Countries::name($flagCode);
    $flagClass = $class ?? 'w-4 h-3';
@endphp
@if($flagUrl && $flagName)
    {{-- The tooltip (title) shows "Country: Bangladesh" on hover, on the
         profile form, post bylines and public profiles alike. --}}
    <span class="inline-flex items-center gap-1 align-middle" title="Country: {{ $flagName }}">
        <img src="{{ $flagUrl }}" alt="{{ $flagName }} flag" width="20" height="15" loading="lazy" decoding="async" class="{{ $flagClass }} object-cover inline-block border border-slate-200 dark:border-slate-600">
        @if(!empty($showName))<span class="text-xs text-slate-500 dark:text-slate-400">{{ $flagName }}</span>@endif
    </span>
@endif

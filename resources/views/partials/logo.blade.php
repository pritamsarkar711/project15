{{-- Site logo: uploaded light/dark logos from admin settings, or text-only huvanti.com fallback --}}
@php
    $logoLight = setting('site_logo_light');
    $logoDark = setting('site_logo_dark');
    $siteName = setting('site_name', 'huvanti.com');
    $base = preg_replace('/\.com$/i', '', $siteName);
    $suffix = strtolower(substr($siteName, -4)) === '.com' ? '.com' : '';
    // Symmetric fallback: if only one variant is uploaded, use it for both modes.
    $effectiveLight = $logoLight ?: $logoDark;
    $effectiveDark  = $logoDark  ?: $logoLight;
@endphp
@if($effectiveLight || $effectiveDark)
    @if($effectiveLight)<img src="{{ asset('storage/'.$effectiveLight) }}" alt="{{ $siteName }}" class="{{ $class ?? 'h-8' }} w-auto block dark:hidden" loading="eager">@endif
    @if($effectiveDark && $effectiveDark !== $effectiveLight)<img src="{{ asset('storage/'.$effectiveDark) }}" alt="{{ $siteName }}" class="{{ $class ?? 'h-8' }} w-auto hidden dark:block" loading="eager">@endif
@else
    <span class="{{ $textClass ?? 'text-[21px]' }} font-extrabold tracking-tight text-slate-900 dark:text-white">{{ $base }}<span class="font-medium text-emerald-600 dark:text-emerald-300">{{ $suffix }}</span></span>
@endif

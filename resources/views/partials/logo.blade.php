{{-- Site logo: uploaded light/dark logos from admin settings, or text-only huvanti.com fallback.
     Pass ['onDark' => true] when placing the logo on a dark surface (footer, green hero):
     the text fallback then renders white instead of slate-900. Uploaded images are used
     as-is (an admin uploading a logo manages its own contrast via the light/dark pair). --}}
@php
    $logoLight = setting('site_logo_light');
    $logoDark = setting('site_logo_dark');
    $siteName = setting('site_name', 'huvanti.com');
    $base = preg_replace('/\.com$/i', '', $siteName);
    $suffix = strtolower(substr($siteName, -4)) === '.com' ? '.com' : '';
    // Symmetric fallback: if only one variant is uploaded, use it for both modes.
    $effectiveLight = $logoLight ?: $logoDark;
    $effectiveDark  = $logoDark  ?: $logoLight;
    $onDark = !empty($onDark);
@endphp
@if($effectiveLight || $effectiveDark)
    @if($effectiveLight)<img src="{{ asset('storage/'.$effectiveLight) }}" alt="{{ $siteName }}" class="{{ $class ?? 'h-8' }} w-auto block dark:hidden" loading="eager">@endif
    @if($effectiveDark && $effectiveDark !== $effectiveLight)<img src="{{ asset('storage/'.$effectiveDark) }}" alt="{{ $siteName }}" class="{{ $class ?? 'h-8' }} w-auto hidden dark:block" loading="eager">@endif
@else
    @if($onDark)
        <span class="{{ $textClass ?? 'text-[21px]' }} font-extrabold tracking-tight text-white">{{ $base }}<span class="font-medium text-emerald-300">{{ $suffix }}</span></span>
    @else
        <span class="{{ $textClass ?? 'text-[21px]' }} font-extrabold tracking-tight text-slate-900 dark:text-white">{{ $base }}<span class="font-medium text-emerald-600 dark:text-emerald-300">{{ $suffix }}</span></span>
    @endif
@endif

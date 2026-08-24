@php
    use App\Support\LucideIcons;
    $iconKey = $icon ?? 'newspaper';
    $iconSvg = LucideIcons::get($iconKey);
    if ($iconSvg === null) {
        $iconKey = 'newspaper';
        $iconSvg = LucideIcons::get('newspaper');
    }
@endphp
<svg class="{{ $class ?? 'w-5 h-5' }}" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">{!! $iconSvg !!}</svg>

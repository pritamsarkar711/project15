@php
    use App\Support\LucideIcons;
    $iconKey = $category->icon ?? 'newspaper';
    $iconSvg = LucideIcons::get($iconKey);
    if ($iconSvg === null) {
        $iconKey = 'newspaper';
        $iconSvg = LucideIcons::get('newspaper');
    }
@endphp
<svg class="{{ $class ?? 'w-5 h-5' }} shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true">{!! $iconSvg !!}</svg>

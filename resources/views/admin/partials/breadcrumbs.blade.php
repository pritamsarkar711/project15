@php
    // Pass $crumbs as an array of ['label' => 'Posts', 'route' => 'admin.posts.index', 'params' => []]
    // Last item is rendered as plain text (no link).
    $crumbs = $crumbs ?? [];
    $defaultCrumbs = [
        ['label' => 'Dashboard', 'route' => 'admin.dashboard'],
    ];
    $allCrumbs = array_merge($defaultCrumbs, $crumbs);
@endphp
<nav aria-label="Breadcrumb" class="mb-4">
    <ol class="flex items-center flex-wrap gap-1.5 text-[13px] text-slate-400 dark:text-slate-500">
        @foreach($allCrumbs as $i => $c)
            @php
                $isLast = ($i === count($allCrumbs) - 1);
                $url = isset($c['route']) ? route($c['route'], $c['params'] ?? []) : ($c['url'] ?? '#');
            @endphp
            @if($isLast)
                <li class="font-semibold text-slate-800 dark:text-slate-200">{{ $c['label'] }}</li>
            @else
                <li>
                    <a href="{{ $url }}" class="hover:text-[#2E7856] dark:hover:text-[#6FB393] transition">{{ $c['label'] }}</a>
                </li>
                <li aria-hidden="true" class="flex items-center"><svg class="w-3.5 h-3.5 text-slate-300 dark:text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m9 6 6 6-6 6"/></svg></li>
            @endif
        @endforeach
    </ol>
</nav>

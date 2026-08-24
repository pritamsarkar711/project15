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
    <ol class="flex items-center flex-wrap gap-1 text-xs text-slate-500 dark:text-slate-400">
        @foreach($allCrumbs as $i => $c)
            @php
                $isLast = ($i === count($allCrumbs) - 1);
                $url = isset($c['route']) ? route($c['route'], $c['params'] ?? []) : ($c['url'] ?? '#');
            @endphp
            @if($isLast)
                <li class="font-semibold text-slate-700 dark:text-slate-200 px-2 py-1 bg-slate-100 dark:bg-slate-800">{{ $c['label'] }}</li>
            @else
                <li>
                    <a href="{{ $url }}" class="hover:text-[#0C3B2E] dark:hover:text-emerald-300 px-2 py-1 transition">{{ $c['label'] }}</a>
                </li>
                <li aria-hidden="true" class="text-slate-300 dark:text-slate-600">/</li>
            @endif
        @endforeach
    </ol>
</nav>

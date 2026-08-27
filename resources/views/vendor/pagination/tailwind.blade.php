@if ($paginator->hasPages())
    <nav role="navigation" aria-label="{{ __('Pagination Navigation') }}">
        <div class="flex flex-wrap gap-2 items-center justify-center">
            @if ($paginator->onFirstPage())
                <span class="inline-flex items-center px-4 h-10 text-sm font-medium text-slate-400 dark:text-slate-600 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 cursor-not-allowed select-none">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    <span class="ml-1 hidden sm:inline">{!! __('pagination.previous') !!}</span>
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="inline-flex items-center px-4 h-10 text-sm font-medium text-slate-700 dark:text-slate-300 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800 hover:text-[#0C3B2E] dark:hover:text-emerald-300 transition select-none">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    <span class="ml-1 hidden sm:inline">{!! __('pagination.previous') !!}</span>
                </a>
            @endif

            @foreach ($elements as $element)
                @if (is_string($element))
                    <span class="w-10 h-10 flex items-center justify-center text-slate-400 dark:text-slate-500 select-none">{{ $element }}</span>
                @endif
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span aria-current="page" class="inline-flex items-center justify-center w-10 h-10 text-sm font-bold text-white bg-[#0C3B2E] shadow-sm select-none">{{ $page }}</span>
                        @else
                            <a href="{{ $url }}" class="inline-flex items-center justify-center w-10 h-10 text-sm font-medium text-slate-700 dark:text-slate-300 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800 hover:text-[#0C3B2E] dark:hover:text-emerald-300 transition select-none">{{ $page }}</a>
                        @endif
                    @endforeach
                @endif
            @endforeach

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="inline-flex items-center px-4 h-10 text-sm font-medium text-slate-700 dark:text-slate-300 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800 hover:text-[#0C3B2E] dark:hover:text-emerald-300 transition select-none">
                    <span class="mr-1 hidden sm:inline">{!! __('pagination.next') !!}</span>
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
            @else
                <span class="inline-flex items-center px-4 h-10 text-sm font-medium text-slate-400 dark:text-slate-600 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 cursor-not-allowed select-none">
                    <span class="mr-1 hidden sm:inline">{!! __('pagination.next') !!}</span>
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </span>
            @endif
        </div>
    </nav>
@endif

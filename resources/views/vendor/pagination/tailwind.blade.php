@if ($paginator->hasPages())
    <nav role="navigation" aria-label="{{ __('Pagination Navigation') }}">
        <div class="flex flex-wrap gap-1.5 items-center justify-center">
            @if ($paginator->onFirstPage())
                <span class="inline-flex items-center px-3.5 h-9 text-sm font-medium text-slate-400 dark:text-slate-600 bg-white dark:bg-[#16181d] border border-[#e6e8ee] dark:border-[#262a33] rounded-lg cursor-not-allowed select-none">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    <span class="ml-1 hidden sm:inline">{!! __('pagination.previous') !!}</span>
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="inline-flex items-center px-3.5 h-9 text-sm font-medium text-slate-700 dark:text-slate-300 bg-white dark:bg-[#16181d] border border-[#e6e8ee] dark:border-[#262a33] rounded-lg hover:bg-[#f7f8fa] dark:hover:bg-[#1c1f26] hover:text-[var(--brand)] dark:hover:text-[var(--brand-light)] transition select-none">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    <span class="ml-1 hidden sm:inline">{!! __('pagination.previous') !!}</span>
                </a>
            @endif

            @foreach ($elements as $element)
                @if (is_string($element))
                    <span class="w-9 h-9 flex items-center justify-center text-sm text-slate-400 dark:text-slate-500 select-none">{{ $element }}</span>
                @endif
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span aria-current="page" class="inline-flex items-center justify-center w-9 h-9 text-sm font-bold text-white bg-[#16181d] dark:bg-white dark:text-[#101319] rounded-lg shadow-sm select-none">{{ $page }}</span>
                        @else
                            <a href="{{ $url }}" class="inline-flex items-center justify-center w-9 h-9 text-sm font-medium text-slate-700 dark:text-slate-300 bg-white dark:bg-[#16181d] border border-[#e6e8ee] dark:border-[#262a33] rounded-lg hover:bg-[#f7f8fa] dark:hover:bg-[#1c1f26] hover:text-[var(--brand)] dark:hover:text-[var(--brand-light)] transition select-none">{{ $page }}</a>
                        @endif
                    @endforeach
                @endif
            @endforeach

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="inline-flex items-center px-3.5 h-9 text-sm font-medium text-slate-700 dark:text-slate-300 bg-white dark:bg-[#16181d] border border-[#e6e8ee] dark:border-[#262a33] rounded-lg hover:bg-[#f7f8fa] dark:hover:bg-[#1c1f26] hover:text-[var(--brand)] dark:hover:text-[var(--brand-light)] transition select-none">
                    <span class="mr-1 hidden sm:inline">{!! __('pagination.next') !!}</span>
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
            @else
                <span class="inline-flex items-center px-3.5 h-9 text-sm font-medium text-slate-400 dark:text-slate-600 bg-white dark:bg-[#16181d] border border-[#e6e8ee] dark:border-[#262a33] rounded-lg cursor-not-allowed select-none">
                    <span class="mr-1 hidden sm:inline">{!! __('pagination.next') !!}</span>
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </span>
            @endif
        </div>
    </nav>
@endif

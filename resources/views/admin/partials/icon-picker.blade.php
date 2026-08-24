@php
    use App\Support\LucideIcons;
    use App\Models\Category;
    $allIcons = LucideIcons::all();
    $current = $current ?? old('icon', $category->icon ?? 'newspaper');
    // Group icons by category for easier scanning
    $groups = [
        'Tech / Digital'     => ['cpu','smartphone','laptop','code','database','cloud','wifi','terminal','binary'],
        'Lifestyle / General' => ['newspaper','book','book-open','music','camera','film','tv','headphones','palette','pen-tool'],
        'Money / Business'  => ['banknote','wallet','credit-card','briefcase','chart-line','trending-up','coins','store','building','landmark'],
        'Travel / Places'    => ['plane','train','car','map','compass','globe','mountain','beach','tree-palm','tent'],
        'Food / Kitchen'    => ['utensils','coffee','wine','ice-cream','apple','carrot'],
        'Health / Fitness'  => ['heart-pulse','dumbbell','activity','brain','stethoscope','pill','cross','bone'],
        'Learning / Growth' => ['graduation-cap','lightbulb','flask-conical','atom','microscope','telescope'],
        'Mood / People'     => ['sun','moon','sparkles','smile','users','user','baby','hand-helping'],
        'Misc'              => ['clock','calendar','gift','key','puzzle','fingerprint','shield','flag','tag'],
    ];
@endphp
<div class="icon-picker-wrapper" data-icon-picker>
    <input type="hidden" name="icon" value="{{ $current }}" id="icon-picker-value">

    {{-- Search box --}}
    <div class="relative mb-3">
        <svg class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.3-4.3"/></svg>
        <input type="text" data-icon-search placeholder="Search icons by name..." class="w-full h-9 pl-9 pr-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-sm focus:outline-none focus:border-[#0C3B2E]">
    </div>

    {{-- Selected preview + clear filter chip --}}
    <div class="flex items-center gap-3 mb-4 pb-3 border-b border-slate-100 dark:border-slate-700/50">
        <div class="w-12 h-12 bg-[#0C3B2E] text-white flex items-center justify-center" data-icon-preview>
            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">{!! $allIcons[$current] ?? $allIcons['newspaper'] !!}</svg>
        </div>
        <div class="flex-1 min-w-0">
            <div class="text-xs text-slate-500 dark:text-slate-400">Selected:</div>
            <div class="text-sm font-semibold text-slate-900 dark:text-white truncate" data-icon-name>{{ $current }}</div>
        </div>
    </div>

    {{-- Grouped icon grid — full width --}}
    <div class="max-h-[360px] overflow-y-auto pr-1" data-icon-scroll>
        @foreach($groups as $groupName => $keys)
        <div class="mb-4" data-icon-group>
            <div class="text-[10px] uppercase tracking-wider text-slate-400 dark:text-slate-500 font-semibold mb-2">{{ $groupName }}</div>
            <div class="grid grid-cols-4 sm:grid-cols-6 md:grid-cols-8 lg:grid-cols-10 gap-1.5">
                @foreach($keys as $iconKey)
                    @php
                        if (!isset($allIcons[$iconKey])) continue;
                        $svgInner = $allIcons[$iconKey];
                    @endphp
                    <button type="button" data-icon-option="{{ $iconKey }}" class="aspect-square border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/60 text-slate-500 dark:text-slate-400 flex items-center justify-center hover:border-emerald-400 hover:text-emerald-700 dark:hover:text-emerald-300 transition {{ $current === $iconKey ? '!border-[#0C3B2E] !bg-emerald-50 dark:!bg-emerald-500/10 !text-emerald-700 dark:!text-emerald-300' : '' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true">{!! $svgInner !!}</svg>
                    </button>
                @endforeach
            </div>
        </div>
        @endforeach
    </div>

    {{-- "No results" placeholder when search yields nothing --}}
    <p class="text-xs text-slate-500 mt-2 hidden" data-icon-empty>No icons match your search.</p>
</div>

@push('scripts')
<script>
(function(){
    const root = document.querySelector('[data-icon-picker]');
    if (!root) return;
    const hidden = root.querySelector('#icon-picker-value');
    const search = root.querySelector('[data-icon-search]');
    const preview = root.querySelector('[data-icon-preview]');
    const nameEl = root.querySelector('[data-icon-name]');
    const empty = root.querySelector('[data-icon-empty]');
    const options = root.querySelectorAll('[data-icon-option]');
    const groups = root.querySelectorAll('[data-icon-group]');
    const iconSvgs = {};
    @foreach($allIcons as $key => $svg)
        iconSvgs[@json($key)] = @json($svg);
    @endforeach

    function selectIcon(key){
        if (!iconSvgs[key]) return;
        hidden.value = key;
        if (preview) preview.innerHTML = '<svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">' + iconSvgs[key] + '</svg>';
        if (nameEl) nameEl.textContent = key;
        options.forEach(o => {
            const isActive = (o.getAttribute('data-icon-option') === key);
            o.classList.toggle('!border-[#0C3B2E]', isActive);
            o.classList.toggle('!bg-emerald-50', isActive);
            o.classList.toggle('dark:!bg-emerald-500/10', isActive);
            o.classList.toggle('!text-emerald-700', isActive);
            o.classList.toggle('dark:!text-emerald-300', isActive);
        });
    }
    options.forEach(o => o.addEventListener('click', () => selectIcon(o.getAttribute('data-icon-option'))));

    if (search){
        search.addEventListener('input', e => {
            const q = e.target.value.toLowerCase().trim();
            let visibleCount = 0;
            options.forEach(o => {
                const key = o.getAttribute('data-icon-option').toLowerCase();
                const show = (q === '' || key.includes(q));
                o.style.display = show ? '' : 'none';
                if (show) visibleCount++;
            });
            // Hide group headers if all their icons are hidden
            groups.forEach(g => {
                const visible = g.querySelectorAll('[data-icon-option]:not([style*="display: none"])');
                g.style.display = visible.length ? '' : 'none';
            });
            if (empty) empty.classList.toggle('hidden', visibleCount > 0);
        });
    }
})();
</script>
@endpush

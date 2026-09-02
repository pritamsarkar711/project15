@php
    // Reusable editor add-on: RankMath-style focus keyword + live SEO score
    // panel + AI writing assistant. Include with:
    //   @include('partials.seo-ai-panel', ['post' => $post ?? null, 'aiEndpoint' => route('author.ai.generate'), 'aiEnabled' => $aiEnabled])
    $seoPost = $post ?? null;
    $aiOn = (bool) ($aiEnabled ?? false);
    $aiBtn = 'h-9 px-3 text-xs font-semibold rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-200 hover:border-[var(--brand)] hover:text-[var(--brand-ink)] dark:hover:text-[var(--brand-light)] transition inline-flex items-center gap-1.5';
@endphp
<input type="hidden" data-seo-ai-root data-ai-endpoint="{{ $aiEndpoint }}" data-ai-enabled="{{ $aiOn ? '1' : '0' }}">

<div class="panel-card p-6 space-y-4" data-seo-suite>
    {{-- Card header: keeps the suite consistent with every other panel card --}}
    <div class="flex items-center gap-2">
        <svg class="w-4 h-4 text-[var(--brand)] dark:text-[var(--brand-light)] shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.35-4.35"/><circle cx="11" cy="11" r="7"/></svg>
        <h3 class="font-semibold text-slate-900 dark:text-white">SEO</h3>
    </div>
    {{-- Focus keyword --}}
    <div>
        <label class="text-sm font-semibold text-slate-900 dark:text-white">Focus keyword</label>
        <input type="text" name="focus_keyword" value="{{ old('focus_keyword', $seoPost?->focus_keyword ?? '') }}" placeholder="e.g. best budget phones"
               class="mt-1 w-full h-10 px-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-sm text-slate-900 dark:text-white">
    </div>

    {{-- Live SEO score (rendered by public/js/seo-analyzer.js) --}}
    <div id="seo-score-panel"></div>
</div>

@if($aiOn)
    <div class="panel-card p-6 space-y-3">
        <div class="flex items-center gap-2">
            <svg class="w-4 h-4 text-[var(--brand)] dark:text-[var(--brand-light)]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09Z"/></svg>
            <h3 class="font-semibold text-slate-900 dark:text-white">AI Assistant</h3>
            <span class="text-[10px] font-bold uppercase tracking-wider text-[var(--brand-ink)] dark:text-[var(--brand-light)] bg-[var(--brand-tint)] dark:bg-[var(--brand-tint-dark)] px-1.5 py-0.5 rounded">Beta</span>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
            <button type="button" data-ai-action="meta_title" data-ai-target="[name=&quot;meta_title&quot;]" class="{{ $aiBtn }}">
                <svg class="w-3.5 h-3.5 shrink-0 text-[var(--brand)] dark:text-[var(--brand-light)]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09Z"/></svg>
                Meta title</button>
            <button type="button" data-ai-action="meta_description" data-ai-target="[name=&quot;meta_description&quot;]" class="{{ $aiBtn }}">
                <svg class="w-3.5 h-3.5 shrink-0 text-[var(--brand)] dark:text-[var(--brand-light)]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 5h18M3 12h18M3 19h10"/></svg>
                Meta description</button>
            <button type="button" data-ai-action="keywords" data-ai-target="[name=&quot;focus_keyword&quot;]" class="{{ $aiBtn }}">
                <svg class="w-3.5 h-3.5 shrink-0 text-[var(--brand)] dark:text-[var(--brand-light)]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.3-4.3"/></svg>
                Focus keyword</button>
            <button type="button" data-ai-action="excerpt" data-ai-target="[name=&quot;excerpt&quot;]" class="{{ $aiBtn }}">
                <svg class="w-3.5 h-3.5 shrink-0 text-[var(--brand)] dark:text-[var(--brand-light)]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h8"/></svg>
                Excerpt</button>
            <button type="button" data-ai-action="faq" data-ai-output="#ai-faq-output" class="{{ $aiBtn }}">
                <svg class="w-3.5 h-3.5 shrink-0 text-[var(--brand)] dark:text-[var(--brand-light)]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><path stroke-linecap="round" stroke-linejoin="round" d="M12 17h.01M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                Suggest FAQ</button>
            <button type="button" data-ai-action="ask" data-ai-question="#ai-ask-input" data-ai-output="#ai-ask-output" class="h-9 px-3 text-xs font-semibold rounded-lg bg-[var(--brand)] hover:bg-[var(--brand-strong)] text-white transition inline-flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5Z"/></svg>
                Ask AI</button>
        </div>

        {{-- Free-form ask --}}
        <div class="pt-1 space-y-2">
            <input type="text" id="ai-ask-input" placeholder="Ask AI, e.g. &quot;give me 5 title ideas&quot;" class="w-full h-10 px-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-sm text-slate-900 dark:text-white">
            <pre id="ai-ask-output" class="hidden whitespace-pre-wrap text-xs bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 p-3 max-h-56 overflow-y-auto text-slate-700 dark:text-slate-200"></pre>
            <pre id="ai-faq-output" class="hidden whitespace-pre-wrap text-xs bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 p-3 text-slate-700 dark:text-slate-200"></pre>
        </div>
    </div>
@endif

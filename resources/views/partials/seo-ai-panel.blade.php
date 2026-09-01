@php
    // Reusable editor add-on: RankMath-style focus keyword + live SEO score
    // panel + AI writing assistant. Include with:
    //   @include('partials.seo-ai-panel', ['post' => $post ?? null, 'aiEndpoint' => route('author.ai.generate'), 'aiEnabled' => $aiEnabled])
    $seoPost = $post ?? null;
    $aiOn = (bool) ($aiEnabled ?? false);
@endphp
<input type="hidden" data-seo-ai-root data-ai-endpoint="{{ $aiEndpoint }}" data-ai-enabled="{{ $aiOn ? '1' : '0' }}">

<div class="panel-card p-6 space-y-4" data-seo-suite>
    {{-- Focus keyword --}}
    <div>
        <label class="text-sm font-semibold text-slate-900 dark:text-white">Focus keyword</label>
        <input type="text" name="focus_keyword" value="{{ old('focus_keyword', $seoPost?->focus_keyword ?? '') }}" placeholder="e.g. best budget phones"
               class="mt-1 w-full h-10 px-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-sm text-slate-900 dark:text-white">
        <p class="text-xs text-slate-400 dark:text-slate-500 mt-1">The main search phrase this post should rank for — the live SEO score below reacts to it instantly.</p>
    </div>

    {{-- Live SEO score (rendered by public/js/seo-analyzer.js) --}}
    <div id="seo-score-panel"></div>
</div>

@if($aiOn)
    <div class="panel-card p-6 space-y-3">
        <div class="flex items-center gap-2">
            <svg class="w-4 h-4 text-[#2E7856] dark:text-[#6FB393]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09Z"/></svg>
            <h3 class="font-semibold text-slate-900 dark:text-white">AI Assistant</h3>
            <span class="text-[10px] font-bold uppercase tracking-wider text-[#1F513A] dark:text-[#6FB393] bg-[#E9F2EE] dark:bg-[#233b30] px-1.5 py-0.5 rounded">Beta</span>
        </div>
        <p class="text-xs text-slate-500 dark:text-slate-400">Write (or start writing) your post, then let the AI draft your SEO fields. You can always edit the suggestions before saving.</p>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
            <button type="button" data-ai-action="meta_title" data-ai-target="[name=&quot;meta_title&quot;]" class="h-9 px-3 text-xs font-semibold rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-200 hover:border-[#2E7856] hover:text-[#1F513A] dark:hover:text-[#6FB393] transition text-left">✨ Suggest meta title</button>
            <button type="button" data-ai-action="meta_description" data-ai-target="[name=&quot;meta_description&quot;]" class="h-9 px-3 text-xs font-semibold rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-200 hover:border-[#2E7856] hover:text-[#1F513A] dark:hover:text-[#6FB393] transition text-left">✨ Suggest meta description</button>
            <button type="button" data-ai-action="keywords" data-ai-target="[name=&quot;focus_keyword&quot;]" class="h-9 px-3 text-xs font-semibold rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-200 hover:border-[#2E7856] hover:text-[#1F513A] dark:hover:text-[#6FB393] transition text-left">✨ Suggest focus keyword</button>
            <button type="button" data-ai-action="excerpt" data-ai-target="[name=&quot;excerpt&quot;]" class="h-9 px-3 text-xs font-semibold rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-200 hover:border-[#2E7856] hover:text-[#1F513A] dark:hover:text-[#6FB393] transition text-left">✨ Write excerpt</button>
            <button type="button" data-ai-action="faq" data-ai-output="#ai-faq-output" class="h-9 px-3 text-xs font-semibold rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-200 hover:border-[#2E7856] hover:text-[#1F513A] dark:hover:text-[#6FB393] transition text-left">✨ Suggest an FAQ</button>
            <button type="button" data-ai-action="ask" data-ai-question="#ai-ask-input" data-ai-output="#ai-ask-output" class="h-9 px-3 text-xs font-semibold rounded-lg bg-[#2E7856] hover:bg-[#27654A] text-white transition text-left">🚀 Ask AI</button>
        </div>

        {{-- Free-form ask --}}
        <div class="pt-1 space-y-2">
            <input type="text" id="ai-ask-input" placeholder="Ask anything: 'improve my intro', 'give 5 title ideas'…" class="w-full h-10 px-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-sm text-slate-900 dark:text-white">
            <pre id="ai-ask-output" class="hidden whitespace-pre-wrap text-xs bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 p-3 max-h-56 overflow-y-auto text-slate-700 dark:text-slate-200"></pre>
            <pre id="ai-faq-output" class="hidden whitespace-pre-wrap text-xs bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 p-3 text-slate-700 dark:text-slate-200"></pre>
        </div>
        <p class="text-[11px] text-slate-400 dark:text-slate-500">Powered by the admin-configured AI provider. Your content is sent to the AI service to generate suggestions. Daily limit applies.</p>
    </div>
@endif

@extends('layouts.admin')

@section('title', 'AI Assistant')

@section('admin-breadcrumbs')
    @include('admin.partials.breadcrumbs', ['crumbs' => [
        ['label' => 'AI Assistant'],
    ]])
@endsection

@section('content')
<div class="max-w-4xl space-y-5">

    <div class="panel-card p-6">
        <h3 class="font-semibold text-[#101319] dark:text-white">AI Assistant</h3>
        <p class="text-sm text-slate-500 dark:text-slate-400 mt-1.5">One-click SEO titles, descriptions, keywords, excerpts and FAQs inside the post editor. Works with any OpenAI-compatible API — keys stay encrypted on the server and models fail over automatically.</p>
        <p class="text-xs text-slate-500 dark:text-slate-400 mt-2">Free key: <a href="https://build.nvidia.com" target="_blank" rel="noopener" class="font-semibold text-[#1F513A] dark:text-[#6FB393] hover:underline">build.nvidia.com</a></p>
    </div>

    <form method="POST" action="{{ route('admin.ai.update') }}" class="space-y-5">
        @csrf

        <div class="panel-card p-6 space-y-4">
            <div class="flex items-start justify-between gap-4 flex-wrap">
                <div>
                    <h3 class="font-semibold text-[#101319] dark:text-white">Assistant</h3>
                    <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Master switch for the whole AI feature.</p>
                </div>
                <label class="inline-flex items-center gap-2 cursor-pointer shrink-0">
                    <span class="relative inline-flex shrink-0">
                        <input type="checkbox" name="ai_assistant_enabled" value="1" {{ old('ai_assistant_enabled', $enabled ? '1' : '0') === '1' ? 'checked' : '' }} class="peer sr-only">
                        <span class="block w-11 h-6 rounded-full bg-slate-200 dark:bg-slate-700 peer-checked:bg-[#2E7856] transition-colors"></span>
                        <span class="pointer-events-none absolute top-0.5 left-0.5 w-5 h-5 rounded-full bg-white shadow transition-transform peer-checked:translate-x-5"></span>
                    </span>
                    <span class="text-sm font-semibold {{ $enabled ? 'text-[#1F513A] dark:text-[#6FB393]' : 'text-slate-500' }}">{{ $enabled ? 'Enabled' : 'Disabled' }}</span>
                </label>
            </div>
            <label class="inline-flex items-center gap-2 text-sm text-slate-600 dark:text-slate-300 cursor-pointer">
                <input type="checkbox" name="ai_allow_authors" value="1" {{ old('ai_allow_authors', $allowAuthors ? '1' : '0') === '1' ? 'checked' : '' }} class="text-[#27654A]">
                Allow authors (not just admins) to use the assistant
            </label>
        </div>

        <div class="panel-card p-6 space-y-4">
            <h3 class="font-semibold text-[#101319] dark:text-white">Provider & API Key</h3>
            <div>
                <label class="text-sm font-medium">API base URL</label>
                <input type="text" name="ai_api_base_url" value="{{ old('ai_api_base_url', $baseUrl) }}" placeholder="https://integrate.api.nvidia.com/v1" class="mt-1 w-full h-10 px-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-sm font-mono">
                <p class="text-xs text-slate-400 dark:text-slate-500 mt-1">NVIDIA NIM: <code class="font-mono">https://integrate.api.nvidia.com/v1</code> · Groq: <code class="font-mono">https://api.groq.com/openai/v1</code> · OpenRouter: <code class="font-mono">https://openrouter.ai/api/v1</code> · OpenAI: <code class="font-mono">https://api.openai.com/v1</code></p>
            </div>
            <div>
                <label class="text-sm font-medium">API key</label>
                <div class="mt-1 flex items-center gap-2">
                    <input type="password" name="ai_api_key" placeholder="{{ $keyHint ?: 'nvapi-… (paste your key)' }}" autocomplete="new-password" class="flex-1 h-10 px-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-sm font-mono">
                    @if($hasKey)
                        <label class="inline-flex items-center gap-1.5 text-xs text-slate-500 dark:text-slate-400 shrink-0">
                            <input type="checkbox" name="remove_ai_api_key" value="1" class="text-[#27654A]"> Remove
                        </label>
                    @endif
                </div>
                <p class="text-xs text-slate-400 dark:text-slate-500 mt-1">{{ $hasKey ? 'Key saved (encrypted). Leave blank to keep it.' : 'No key saved yet.' }}</p>
            </div>
            <div>
                <label class="text-sm font-medium">Daily limit per user</label>
                <input type="number" name="ai_daily_limit" min="0" max="1000" value="{{ old('ai_daily_limit', $dailyLimit) }}" class="mt-1 w-40 h-10 px-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-sm">
                <p class="text-xs text-slate-400 dark:text-slate-500 mt-1">Generations per user per day. 0 = blocked.</p>
            </div>
        </div>

        <div class="panel-card p-6 space-y-3">
            <h3 class="font-semibold text-[#101319] dark:text-white">Models (priority order)</h3>
            <p class="text-xs text-slate-500 dark:text-slate-400">One per line — on failure the next model takes over. Browse IDs at <a href="https://build.nvidia.com/models" target="_blank" rel="noopener" class="text-[#1F513A] dark:text-[#6FB393] hover:underline">build.nvidia.com/models</a>.</p>
            <textarea name="ai_models" rows="7" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-sm font-mono">{{ old('ai_models', $models) }}</textarea>
        </div>

        <button type="submit" class="h-11 px-6 rounded-lg bg-[#2E7856] hover:bg-[#27654A] text-white font-semibold transition">Save AI Settings</button>
    </form>

    {{-- Model test bench --}}
    <div class="panel-card p-6">
        <div class="flex items-center justify-between gap-3 flex-wrap">
            <div>
                <h3 class="font-semibold text-[#101319] dark:text-white">Model Health Check</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Saves settings, then pings each model.</p>
            </div>
            <button type="button" id="ai-test-btn" class="h-10 px-5 rounded-lg border border-[#2E7856]/50 text-[#2E7856] dark:text-[#6FB393] font-semibold text-sm hover:bg-[#2E7856]/5 transition">Test all models</button>
        </div>
        <div id="ai-test-results" class="mt-4 hidden space-y-2"></div>
    </div>
</div>

@push('scripts')
<script>
    document.getElementById('ai-test-btn')?.addEventListener('click', function(){
        var btn = this;
        var box = document.getElementById('ai-test-results');
        btn.disabled = true;
        var prev = btn.textContent;
        btn.textContent = 'Saving & testing…';
        box.classList.add('hidden');
        box.innerHTML = '';

        // Save first so freshly typed keys/models are what we test.
        var form = document.querySelector('form[action="{{ route('admin.ai.update') }}"]');
        var save = fetch(form.action, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
            body: new FormData(form),
            credentials: 'same-origin'
        });

        save.then(function(){
            return fetch('{{ route('admin.ai.test') }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                }
            });
        }).then(function(r){ return r.json(); }).then(function(data){
            (data.results || []).forEach(function(res){
                var row = document.createElement('div');
                row.className = 'flex items-center justify-between gap-3 text-sm border border-[#e6e8ee] dark:border-[#22262e] p-3';
                row.innerHTML = '<span class="font-mono text-xs text-slate-600 dark:text-slate-300 truncate">' + res.model + '</span>' +
                    '<span class="' + (res.ok ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400') + ' font-semibold text-xs text-right">' + res.message + '</span>';
                box.appendChild(row);
            });
            box.classList.remove('hidden');
        }).catch(function(){
            alert('Test failed — network error.');
        }).finally(function(){
            btn.disabled = false;
            btn.textContent = prev;
        });
    });
</script>
@endpush
@endsection

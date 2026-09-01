<?php

namespace App\Services\Ai;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Huvanti AI Assistant — an admin-configurable, provider-agnostic LLM client
 * that powers the author-facing writing helper (SEO titles, meta descriptions,
 * focus keywords, excerpts, FAQs, free-form "Ask AI").
 *
 * PROVIDERS
 *   Any OpenAI-compatible /chat/completions endpoint works. The default base
 *   URL is NVIDIA NIM (https://integrate.api.nvidia.com/v1) — build.nvidia.com
 *   gives free API credits and hosts the popular open models (Llama, Mistral,
 *   Mixtral, Gemma, Qwen, DeepSeek, Phi…). Groq, OpenRouter, Together, OpenAI
 *   itself, vLLM, Ollama-with-expose etc. all work by changing the base URL.
 *
 * AUTO MODEL SWITCHING ("auto pick")
 *   The admin lists candidate models in priority order. Each chat call walks
 *   the list; a model that errors, times out or rate-limits is marked DOWN in
 *   the cache for 10 minutes and the next model takes over transparently.
 *   The last known-good model is remembered and tried FIRST for speed.
 *
 * SECURITY MODEL
 *   - The API key is stored ENCRYPTED AT REST (Crypt, APP_KEY) in settings.
 *   - It is decrypted only here, only at call time, and NEVER leaves the
 *     server: the browser talks to /author-dashboard/ai/generate, which
 *     proxies to the provider. No key, base URL or model list is exposed.
 *   - Users are rate-limited (throttle middleware) and quota-limited per day.
 */
class AiAssistantService
{
    public const BASE_URL_DEFAULT = 'https://integrate.api.nvidia.com/v1';

    /**
     * Suggested NVIDIA NIM models, verified live against integrate.api.nvidia.com
     * (probed 2026-09). Admins can edit the list freely; Browse more at
     * build.nvidia.com/models or GET /v1/models with your key.
     */
    public const NVIDIA_SUGGESTED_MODELS =
        "openai/gpt-oss-120b\n".
        "moonshotai/kimi-k2.6\n".
        "deepseek-ai/deepseek-v4-flash-0731\n".
        "google/gemma-3-12b-it\n".
        "mistralai/mistral-7b-instruct-v0.3\n".
        "nvidia/llama-3.1-nemotron-70b-instruct\n".
        "meta/llama-3.2-90b-vision-instruct";

    private const DOWN_TTL = 600;          // seconds a failing model is skipped
    private const LAST_GOOD_KEY = 'ai_last_good_model';
    private const DOWN_PREFIX = 'ai_model_down:';

    // ------------------------------------------------------------------
    // Config accessors
    // ------------------------------------------------------------------

    public function enabled(): bool
    {
        return Setting::get('ai_assistant_enabled', '0') === '1' && $this->apiKey() !== '';
    }

    public function enabledForAuthors(): bool
    {
        return $this->enabled() && Setting::get('ai_allow_authors', '1') === '1';
    }

    public function baseUrl(): string
    {
        $url = trim((string) Setting::get('ai_api_base_url', self::BASE_URL_DEFAULT));
        return $url !== '' ? rtrim($url, '/') : self::BASE_URL_DEFAULT;
    }

    public function models(): array
    {
        $raw = (string) Setting::get('ai_models', self::NVIDIA_SUGGESTED_MODELS);
        $list = array_values(array_filter(array_map(
            fn ($m) => trim($m),
            preg_split('/[\n,;]+/', $raw) ?: []
        ), fn ($m) => $m !== ''));
        if ($list) return $list;
        // Fallback: first line of the suggested list.
        $first = trim(strtok(self::NVIDIA_SUGGESTED_MODELS, "\n"));
        return [$first !== '' ? $first : 'openai/gpt-oss-120b'];
    }

    public function dailyLimit(): int
    {
        return max(0, (int) Setting::get('ai_daily_limit', '30'));
    }

    /** Encrypted-at-rest API key handling. */
    public function setApiKey(string $plain): void
    {
        Setting::set('ai_api_key', $plain === '' ? '' : Crypt::encryptString($plain), 'secret', 'ai');
    }

    public function hasApiKey(): bool
    {
        return $this->apiKey() !== '';
    }

    public function apiKey(): string
    {
        $stored = (string) Setting::get('ai_api_key', '');
        if ($stored === '') return '';
        if (!str_starts_with($stored, 'eyJ')) return $stored; // legacy plain value
        try {
            return Crypt::decryptString($stored);
        } catch (\Throwable $e) {
            Log::warning('AI API key could not be decrypted');
            return '';
        }
    }

    public function maskKey(): string
    {
        $k = $this->apiKey();
        return $k === '' ? '' : 'configured — ends "…'.mb_substr($k, -4).'"';
    }

    // ------------------------------------------------------------------
    // Quota
    // ------------------------------------------------------------------

    public function quotaKey(int $userId): string
    {
        return 'ai_quota:'.now()->format('Ymd').':'.$userId;
    }

    public function quotaLeft(int $userId): int
    {
        return max(0, $this->dailyLimit() - (int) Cache::get($this->quotaKey($userId), 0));
    }

    public function consumeQuota(int $userId): void
    {
        $key = $this->quotaKey($userId);
        Cache::put($key, ((int) Cache::get($key, 0)) + 1, now()->endOfDay());
    }

    // ------------------------------------------------------------------
    // Chat — with model failover
    // ------------------------------------------------------------------

    /**
     * Run a chat completion. Tries the last-good model first, then the
     * remaining models in priority order. Throws AiUnavailableException when
     * every candidate fails (the controller turns that into a friendly 503).
     */
    public function chat(array $messages, int $maxTokens = 700, float $temperature = 0.7): string
    {
        $key = $this->apiKey();
        if ($key === '') {
            throw new AiUnavailableException('AI assistant has no API key configured yet.');
        }

        $candidates = $this->orderedModels();
        $errors = [];

        foreach ($candidates as $model) {
            if (Cache::has(self::DOWN_PREFIX.md5($model))) {
                $errors[] = "{$model}: skipped (cooling down)";
                continue;
            }
            try {
                $response = Http::timeout(45)->connectTimeout(10)
                    ->withHeaders([
                        'Authorization' => 'Bearer '.$key,
                        'Accept'        => 'application/json',
                    ])
                    ->post($this->baseUrl().'/chat/completions', [
                        'model'       => $model,
                        'messages'    => $messages,
                        'max_tokens'  => $maxTokens,
                        'temperature' => $temperature,
                        'stream'      => false,
                    ]);

                if ($response->successful()) {
                    $content = (string) data_get($response->json(), 'choices.0.message.content', '');
                    if (trim($content) !== '') {
                        Cache::put(self::LAST_GOOD_KEY, $model, now()->addHours(6));
                        return trim($content);
                    }
                    $errors[] = "{$model}: empty response";
                    $this->markDown($model);
                    continue;
                }

                $status = $response->status();
                $errors[] = "{$model}: HTTP {$status} ".Str::limit($response->body(), 160);
                $this->markDown($model);
                // 401/403 = key problem — the next model won't fix a bad key,
                // but still try: some providers key-gate per model.
                if ($status === 401) break;
            } catch (\Illuminate\Http\Client\ConnectionException $e) {
                $errors[] = "{$model}: connection failed (".$e->getMessage().')';
                $this->markDown($model);
            } catch (\Throwable $e) {
                $errors[] = "{$model}: ".$e->getMessage();
                $this->markDown($model);
            }
        }

        throw new AiUnavailableException('All AI models are unavailable right now. Details: '.implode(' | ', $errors));
    }

    /** Last-good model first, then the admin's priority list. */
    public function orderedModels(): array
    {
        $models = $this->models();
        $lastGood = Cache::get(self::LAST_GOOD_KEY);
        if ($lastGood && in_array($lastGood, $models, true)) {
            return array_values(array_merge([$lastGood], array_diff($models, [$lastGood])));
        }
        return $models;
    }

    private function markDown(string $model): void
    {
        Cache::put(self::DOWN_PREFIX.md5($model), 1, now()->addSeconds(self::DOWN_TTL));
    }

    /** Admin "Test models" — pings each model with a 1-token request. */
    public function testModels(): array
    {
        $key = $this->apiKey();
        if ($key === '') {
            return [['model' => '—', 'ok' => false, 'message' => 'No API key configured.']];
        }
        $out = [];
        foreach ($this->models() as $model) {
            try {
                $r = Http::timeout(30)->connectTimeout(10)
                    ->withHeaders(['Authorization' => 'Bearer '.$key, 'Accept' => 'application/json'])
                    ->post($this->baseUrl().'/chat/completions', [
                        'model'      => $model,
                        'messages'   => [['role' => 'user', 'content' => 'Reply with the single word: OK']],
                        'max_tokens' => 8,
                        'temperature' => 0,
                        'stream'     => false,
                    ]);
                if ($r->successful() && trim((string) data_get($r->json(), 'choices.0.message.content', '')) !== '') {
                    $out[] = ['model' => $model, 'ok' => true, 'message' => 'OK — '.$r->json('model', $model)];
                } else {
                    $out[] = ['model' => $model, 'ok' => false, 'message' => 'HTTP '.$r->status().': '.Str::limit($r->body(), 140)];
                }
            } catch (\Throwable $e) {
                $out[] = ['model' => $model, 'ok' => false, 'message' => $e->getMessage()];
            }
        }
        return $out;
    }
}

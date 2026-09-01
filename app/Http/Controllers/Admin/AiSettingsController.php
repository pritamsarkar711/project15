<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Services\Ai\AiAssistantService;
use Illuminate\Http\Request;

/**
 * Admin → AI Assistant.
 *
 * The admin picks any OpenAI-compatible provider (default: NVIDIA NIM —
 * build.nvidia.com free API key), stores the API key (encrypted at rest,
 * masked in the UI), lists candidate models in priority order and can test
 * them. Authors then get the assistant inside the post editor.
 */
class AiSettingsController extends Controller
{
    public function __construct(private AiAssistantService $ai)
    {
    }

    public function index()
    {
        return view('admin.ai.index', [
            'ai'          => $this->ai,
            'enabled'     => Setting::get('ai_assistant_enabled', '0') === '1',
            'allowAuthors'=> Setting::get('ai_allow_authors', '1') === '1',
            'baseUrl'     => $this->ai->baseUrl(),
            'models'      => (string) Setting::get('ai_models', AiAssistantService::NVIDIA_SUGGESTED_MODELS),
            'dailyLimit'  => $this->ai->dailyLimit(),
            'keyHint'     => $this->ai->maskKey(),
            'hasKey'      => $this->ai->hasApiKey(),
            'suggested'   => AiAssistantService::NVIDIA_SUGGESTED_MODELS,
        ]);
    }

    public function update(Request $request)
    {
        $request->validate([
            'ai_assistant_enabled' => ['nullable', 'in:1'],
            'ai_allow_authors'     => ['nullable', 'in:1'],
            'ai_api_base_url'      => ['nullable', 'string', 'max:255'],
            'ai_models'            => ['nullable', 'string', 'max:2000'],
            'ai_daily_limit'       => ['nullable', 'integer', 'min:0', 'max:1000'],
            'ai_api_key'           => ['nullable', 'string', 'max:600'],
            'remove_ai_api_key'    => ['nullable', 'in:1'],
        ]);

        Setting::set('ai_assistant_enabled', $request->boolean('ai_assistant_enabled') ? '1' : '0', 'text', 'ai');
        Setting::set('ai_allow_authors', $request->boolean('ai_allow_authors') ? '1' : '0', 'text', 'ai');
        Setting::set('ai_api_base_url', trim((string) $request->input('ai_api_base_url', AiAssistantService::BASE_URL_DEFAULT)) ?: AiAssistantService::BASE_URL_DEFAULT, 'text', 'ai');
        Setting::set('ai_models', (string) $request->input('ai_models', AiAssistantService::NVIDIA_SUGGESTED_MODELS), 'text', 'ai');
        Setting::set('ai_daily_limit', (string) (int) ($request->input('ai_daily_limit') ?: 30), 'text', 'ai');

        // Key handling mirrors the SMTP password pattern: blank = keep,
        // checkbox = remove, non-blank = replace (encrypted before storage).
        if ($request->boolean('remove_ai_api_key')) {
            Setting::set('ai_api_key', '', 'secret', 'ai');
        } elseif (trim((string) $request->input('ai_api_key', '')) !== '') {
            $this->ai->setApiKey(trim((string) $request->input('ai_api_key')));
        }

        Setting::flushAllCache();

        return redirect()->route('admin.ai.index')->with('success', 'AI Assistant settings saved.');
    }

    /** AJAX: test every configured model; returns per-model status JSON. */
    public function test()
    {
        return response()->json(['results' => $this->ai->testModels()]);
    }
}

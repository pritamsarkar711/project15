<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Services\Ai\AiAssistantService;
use App\Services\Ai\AiUnavailableException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * Author-facing AI writing assistant (author panel AND admin editor).
 *
 * SECURITY: the browser only ever sees generated TEXT. The provider base
 * URL, API key and model list stay on the server (see AiAssistantService).
 * Abuse protection: auth required + throttle + per-user daily quota.
 */
class AiAssistantController extends Controller
{
    public function __construct(private AiAssistantService $ai)
    {
    }

    public function generate(Request $request): JsonResponse
    {
        $user = $request->user();

        // Admins configured the assistant for staff only? Then authors bounce.
        if (!$this->ai->enabled() || (!$this->ai->enabledForAuthors() && !$user->isAdmin())) {
            return response()->json(['ok' => false, 'message' => 'The AI assistant is currently disabled by the site admin.'], 503);
        }

        if ($this->ai->quotaLeft($user->id) <= 0) {
            return response()->json(['ok' => false, 'message' => 'You have used your AI quota for today. It resets at midnight — happy writing!'], 429);
        }

        $data = $request->validate([
            'action'   => ['required', 'in:meta_title,meta_description,keywords,excerpt,faq,ask'],
            'title'    => ['nullable', 'string', 'max:255'],
            'content'  => ['nullable', 'string', 'max:60000'],
            'keyword'  => ['nullable', 'string', 'max:120'],
            'question' => ['nullable', 'string', 'max:500'],
        ]);

        $content = \App\Services\Social\SocialAutoPostService::plainText($data['content'] ?? '');
        $contentExcerpt = Str::limit($content, 6000); // keep token cost sane
        $title = trim((string) ($data['title'] ?? ''));
        $keyword = trim((string) ($data['keyword'] ?? ''));
        $needsContent = in_array($data['action'], ['meta_title', 'meta_description', 'keywords', 'excerpt', 'faq'], true);

        if ($needsContent && $content === '' && $title === '') {
            return response()->json(['ok' => false, 'message' => 'Write a title or some content first — the AI needs something to work with.'], 422);
        }

        [$system, $userMsg, $maxTokens] = $this->promptFor($data['action'], $title, $contentExcerpt, $keyword, trim((string) ($data['question'] ?? '')));

        try {
            $text = $this->ai->chat(
                [
                    ['role' => 'system', 'content' => $system],
                    ['role' => 'user', 'content' => $userMsg],
                ],
                $maxTokens,
                0.6
            );
        } catch (AiUnavailableException $e) {
            report($e);
            return response()->json(['ok' => false, 'message' => 'The AI service is busy or unreachable right now. Please try again in a few minutes.'], 503);
        }

        $this->ai->consumeQuota($user->id);

        // "ask" returns prose for a modal; structured actions return ONE
        // ready-to-paste value.
        return response()->json([
            'ok'       => true,
            'action'   => $data['action'],
            'result'   => $data['action'] === 'ask'
                ? $text
                : $this->singleValue($text),
            'quota_left' => $this->ai->quotaLeft($user->id),
        ]);
    }

    /** Actions other than "ask" must return exactly one clean line/value. */
    private function singleValue(string $text): string
    {
        $text = trim($text);
        // Models love to prefix "Sure! Here's ..." — strip the chit-chat.
        $text = preg_replace('/^(sure|here(’|\')?s|here is|certainly|of course)[^:\n]{0,60}:\s*/i', '', $text) ?? $text;
        $text = preg_replace('/^["\']|["\']$/', '', trim($text)) ?? $text;
        // If several numbered options were produced, take the first.
        $lines = array_values(array_filter(array_map('trim', explode("\n", $text)), fn ($l) => $l !== ''));
        if (count($lines) > 1) {
            foreach ($lines as $line) {
                $clean = preg_replace('/^([*\-]|\d+[\.\)])\s*/', '', $line) ?? $line;
                $clean = trim($clean, "\"“”");
                if (mb_strlen($clean) > 15) return rtrim($clean, '.');
            }
        }
        return $text;
    }

    /** @return array{0:string,1:string,2:int} system, user message, maxTokens */
    private function promptFor(string $action, string $title, string $content, string $keyword, string $question): array
    {
        $brand = (string) config('app.name', 'Huvanti');
        $base = "You are an expert SEO copywriting assistant for the blog {$brand}. Reply with plain text only — no markdown, no quotes around the answer, no explanations.";

        switch ($action) {
            case 'meta_title':
                return [$base,
                    "Write ONE SEO title for this blog post.\nRules: 50–60 characters, include the focus keyword naturally, no clickbait, title case.\n".($keyword !== '' ? "Focus keyword: {$keyword}\n" : '')."Post title: {$title}\n\nPost content:\n{$content}"];
            case 'meta_description':
                return [$base,
                    "Write ONE meta description for this blog post.\nRules: 130–155 characters, include the focus keyword once, compelling and factual, plain sentence.\n".($keyword !== '' ? "Focus keyword: {$keyword}\n" : '')."Post title: {$title}\n\nPost content:\n{$content}"];
            case 'keywords':
                return [$base,
                    "Extract the SINGLE best focus keyword phrase (2–4 words, lowercase) that best represents this post and that people actually search for.\nPost title: {$title}\n\nPost content:\n{$content}"];
            case 'excerpt':
                return [$base,
                    "Write a short blog excerpt/summary for this post.\nRules: 140–180 characters, one or two sentences, no spoiler of every point, plain text.\nPost title: {$title}\n\nPost content:\n{$content}"];
            case 'faq':
                return [$base,
                    "Based on the post content, write ONE frequently-asked question with its answer.\nFormat exactly:\nQUESTION: <the question>\nANSWER: <a helpful 2–3 sentence answer>\n".($keyword !== '' ? "Focus keyword: {$keyword}\n" : '')."Post title: {$title}\n\nPost content:\n{$content}"];
            default: // ask (free-form)
                return ["You are a helpful writing assistant inside the {$brand} blog editor. Help the author with their question about writing, editing, SEO or grammar. Be concise. Use plain text.",
                    "Post title: ".($title ?: '(untitled)')."\n\nQuestion: ".($question !== '' ? $question : 'Give me one concrete tip to improve this post.'),
                ];
        }
    }
}

<?php

namespace App\Support;

use Illuminate\Support\HtmlString;

/**
 * Production-safe Vite tag renderer.
 *
 * @vite throws a 500 when the manifest is missing, an entry is unknown, or a
 * leftover public/hot file points at a local Vite dev server. On Hostinger
 * that takes down every Blade page. This helper never throws: it prefers the
 * real Vite renderer and falls back to the committed build files.
 */
class ViteAssets
{
    /** Last-resort hashed filenames shipped in public/build/. */
    private const FALLBACK = [
        'resources/css/app.css' => 'assets/app-nvEblEmn.css',
        'resources/js/app.js' => 'assets/app-BvRk9kiK.js',
    ];

    public static function tags(array $entrypoints): HtmlString
    {
        try {
            $hot = public_path('hot');
            if (is_file($hot)) {
                @unlink($hot);
            }

            return new HtmlString((string) app(\Illuminate\Foundation\Vite::class)($entrypoints));
        } catch (\Throwable $e) {
            try {
                report($e);
            } catch (\Throwable $ignored) {
            }

            return new HtmlString(self::fallback($entrypoints));
        }
    }

    private static function fallback(array $entrypoints): string
    {
        $manifest = [];
        $path = public_path('build/manifest.json');
        if (is_file($path)) {
            $decoded = json_decode((string) file_get_contents($path), true);
            if (is_array($decoded)) {
                $manifest = $decoded;
            }
        }

        $html = '';
        foreach ($entrypoints as $entry) {
            $file = $manifest[$entry]['file'] ?? self::FALLBACK[$entry] ?? null;
            if (! is_string($file) || $file === '') {
                continue;
            }
            $href = '/build/'.$file;
            if (str_ends_with($entry, '.css') || str_ends_with($file, '.css')) {
                $html .= '<link rel="stylesheet" href="'.htmlspecialchars($href, ENT_QUOTES, 'UTF-8').'">';
            } else {
                $html .= '<script type="module" src="'.htmlspecialchars($href, ENT_QUOTES, 'UTF-8').'"></script>';
            }
        }

        return $html;
    }
}

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
        'resources/css/app.css' => 'assets/app-a8cb9843.css',
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

    /**
     * <script> tag for the self-made Huvanti rich text editor with automatic
     * cache-busting. The file lives at public/js/huvanti-editor.js and is
     * served directly by Apache (it is NOT part of the Vite build), so its
     * URL never changes on its own — browsers and LiteSpeed keep serving the
     * previously cached copy after a deploy. That is exactly why editor fixes
     * "did not apply" for returning visitors. Appending the file's
     * modification time as ?v=... forces every client to fetch the fresh
     * version whenever the file changes, while still allowing long-lived
     * caching in between deploys.
     */
    public static function editorScript(): HtmlString
    {
        $path = public_path('js/huvanti-editor.js');
        $version = is_file($path) ? (string) filemtime($path) : '1';

        $src = htmlspecialchars(asset('js/huvanti-editor.js').'?v='.$version, ENT_QUOTES, 'UTF-8');

        return new HtmlString('<script src="'.$src.'"></script>');
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

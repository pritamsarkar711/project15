<?php

namespace App\Support;

/**
 * Centralised list of Google-Fonts that can be selected from the admin panel
 * and applied site-wide (frontend + admin). The site default is "Inter"
 * (matching the reference design system); selecting a font here overrides it
 * everywhere on the site.
 *
 * Each entry contains:
 *   - label   : human-friendly name shown in the admin dropdown
 *   - css     : the exact CSS font-family stack to apply on <body>
 *   - google  : the query string fragment for fonts.googleapis.com/css2
 *
 * The full Google Fonts URL is built as:
 *   https://fonts.googleapis.com/css2?family=<google>&display=swap
 */
class FontFamilies
{
    /** @return array<string,array{label:string,css:string,google:string}> */
    public static function all(): array
    {
        return [
            'geist' => [
                'label'  => 'Geist',
                'css'    => "'Geist', ui-sans-serif, system-ui, -apple-system, 'Segoe UI', Roboto, sans-serif",
                'google' => 'Geist:wght@400;500;600;700;800',
            ],
            'work-sans' => [
                'label'  => 'Work Sans',
                'css'    => "'Work Sans', ui-sans-serif, system-ui, sans-serif",
                'google' => 'Work+Sans:wght@400;500;600;700;800',
            ],
            'inter' => [
                'label'  => 'Inter',
                'css'    => "'Inter', ui-sans-serif, system-ui, sans-serif",
                'google' => 'Inter:wght@400;500;600;700;800;900',
            ],
            'roboto' => [
                'label'  => 'Roboto',
                'css'    => "'Roboto', ui-sans-serif, system-ui, sans-serif",
                'google' => 'Roboto:wght@400;500;700;900',
            ],
            'public-sans' => [
                'label'  => 'Public Sans',
                'css'    => "'Public Sans', ui-sans-serif, system-ui, sans-serif",
                'google' => 'Public+Sans:wght@400;500;600;700;800',
            ],
            'jakarta-sans' => [
                'label'  => 'Plus Jakarta Sans',
                'css'    => "'Plus Jakarta Sans', ui-sans-serif, system-ui, sans-serif",
                'google' => 'Plus+Jakarta+Sans:wght@400;500;600;700;800',
            ],
            'fira-sans' => [
                'label'  => 'Fira Sans',
                'css'    => "'Fira Sans', ui-sans-serif, system-ui, sans-serif",
                'google' => 'Fira+Sans:wght@400;500;600;700;800',
            ],
            'open-sans' => [
                'label'  => 'Open Sans',
                'css'    => "'Open Sans', ui-sans-serif, system-ui, sans-serif",
                'google' => 'Open+Sans:wght@400;500;600;700;800',
            ],
            'source-sans-3' => [
                'label'  => 'Source Sans 3',
                'css'    => "'Source Sans 3', ui-sans-serif, system-ui, sans-serif",
                'google' => 'Source+Sans+3:wght@400;500;600;700',
            ],
            'noto-sans' => [
                'label'  => 'Noto Sans',
                'css'    => "'Noto Sans', ui-sans-serif, system-ui, sans-serif",
                'google' => 'Noto+Sans:wght@400;500;600;700;800',
            ],
            'lato' => [
                'label'  => 'Lato',
                'css'    => "'Lato', ui-sans-serif, system-ui, sans-serif",
                'google' => 'Lato:wght@400;700;900',
            ],
            'montserrat' => [
                'label'  => 'Montserrat',
                'css'    => "'Montserrat', ui-sans-serif, system-ui, sans-serif",
                'google' => 'Montserrat:wght@400;500;600;700;800',
            ],
            'poppins' => [
                'label'  => 'Poppins',
                'css'    => "'Poppins', ui-sans-serif, system-ui, sans-serif",
                'google' => 'Poppins:wght@400;500;600;700;800',
            ],
            'manrope' => [
                'label'  => 'Manrope',
                'css'    => "'Manrope', ui-sans-serif, system-ui, sans-serif",
                'google' => 'Manrope:wght@400;500;600;700;800',
            ],
            'sora' => [
                'label'  => 'Sora',
                'css'    => "'Sora', ui-sans-serif, system-ui, sans-serif",
                'google' => 'Sora:wght@400;500;600;700;800',
            ],
            'space-grotesk' => [
                'label'  => 'Space Grotesk',
                'css'    => "'Space Grotesk', ui-sans-serif, system-ui, sans-serif",
                'google' => 'Space+Grotesk:wght@400;500;600;700',
            ],
            'google-sans' => [
                'label'  => 'Google Sans',
                'css'    => "'Google Sans', 'Product Sans', Roboto, ui-sans-serif, system-ui, sans-serif",
                'google' => 'Google+Sans:wght@400;500;700;800',
            ],
        ];
    }

    /** Get the data array for a key, falling back to the Geist default. */
    public static function get(string $key): array
    {
        $all = self::all();
        return $all[$key] ?? $all['geist'];
    }

    /** Convenience: the Google Fonts URL for a given key. */
    public static function googleUrl(string $key): string
    {
        $f = self::get($key);
        return "https://fonts.googleapis.com/css2?family={$f['google']}&display=swap";
    }

    /** Convenience: the CSS font-family stack for a given key. */
    public static function cssStack(string $key): string
    {
        return self::get($key)['css'];
    }

    /** Convenience: list of [key => label] for select dropdowns. */
    public static function options(): array
    {
        $opts = [];
        foreach (self::all() as $key => $f) {
            $opts[$key] = $f['label'];
        }
        return $opts;
    }
}

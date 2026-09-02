<?php

namespace App\Support;

/**
 * Site theme registry.
 *
 * The admin picks one theme in Settings → Appearance and it is stored as the
 * "site_theme" setting. Every layout (public site, admin panel, author panel)
 * prints data-site-theme on <html>, and resources/css/app.css swaps the whole
 * brand palette from that single attribute: buttons, links, hero bands, cards,
 * badges, SEO panel and the dark panel sidebars all recolor together, in both
 * light and dark mode.
 */
class SiteThemes
{
    /**
     * key => [label, hint, swatch colors [main, deep, light]]
     * "default" is intentionally first: it is the original Emerald palette.
     */
    public static function all(): array
    {
        return [
            'default' => [
                'label' => 'Emerald',
                'hint'  => 'The original Huvanti green',
                'swatch' => ['#2E7856', '#173A2A', '#6FB393'],
            ],
            'material' => [
                'label' => 'Material',
                'hint'  => 'Material inspired indigo',
                'swatch' => ['#4F46E5', '#1E1B4B', '#A5B4FC'],
            ],
            'ocean' => [
                'label' => 'Ocean',
                'hint'  => 'Deep calm cyan',
                'swatch' => ['#0E7490', '#164E63', '#67E8F9'],
            ],
            'sunset' => [
                'label' => 'Sunset',
                'hint'  => 'Warm amber and ember',
                'swatch' => ['#C2410C', '#431407', '#FDBA74'],
            ],
        ];
    }

    public static function keys(): array
    {
        return array_keys(self::all());
    }

    /**
     * Resolve a stored setting value to a valid theme key. Unknown or empty
     * values always fall back to the default theme, so a stale database row
     * can never blank the site.
     */
    public static function validOrDefault(?string $key): string
    {
        return $key && array_key_exists($key, self::all()) ? $key : 'default';
    }
}

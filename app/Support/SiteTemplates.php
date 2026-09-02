<?php

namespace App\Support;

/**
 * Site template registry.
 *
 * A template is the site's design language: corner geometry, elevation,
 * typography and surface treatment. It is independent of the color theme
 * (SiteThemes recolors; SiteTemplates reshapes), so any template pairs with
 * any palette. The admin picks one in Settings → Appearance, it is stored as
 * the "site_template" setting, printed as data-site-template on <html> by
 * every layout (public site, admin panel, author panel) and resolved by the
 * SITE TEMPLATES block in resources/css/app.css.
 */
class SiteTemplates
{
    /**
     * key => [label, hint]. "classic" is intentionally first: it is the
     * original Huvanti design and the fallback for unknown values.
     */
    public static function all(): array
    {
        return [
            'classic' => [
                'label' => 'Classic',
                'hint'  => 'The original Huvanti design',
            ],
            'material' => [
                'label' => 'Material',
                'hint'  => 'Soft shapes, pill actions, layered depth',
            ],
            'editorial' => [
                'label' => 'Editorial',
                'hint'  => 'Serif headlines, crisp edges, hairline rules',
            ],
        ];
    }

    public static function keys(): array
    {
        return array_keys(self::all());
    }

    /**
     * Resolve a stored setting value to a valid template key. Unknown or
     * empty values always fall back to the classic template, so a stale
     * database row can never break the site's design.
     */
    public static function validOrDefault(?string $key): string
    {
        return $key && array_key_exists($key, self::all()) ? $key : 'classic';
    }
}

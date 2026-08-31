<?php

namespace App\Support;

use App\Models\Setting;

/**
 * Read the active site-wide font family. Falls back to "geist" (the site
 * default, matching the reference design system) if no setting is configured.
 */
class SiteFont
{
    public static function key(): string
    {
        try {
            $value = Setting::get('site_font_family', 'geist');
            if (! is_string($value) || $value === '') {
                return 'geist';
            }

            return $value;
        } catch (\Throwable $e) {
            return 'geist';
        }
    }

    public static function googleUrl(): string
    {
        return FontFamilies::googleUrl(self::key());
    }

    public static function cssStack(): string
    {
        return FontFamilies::cssStack(self::key());
    }

    public static function label(): string
    {
        return FontFamilies::get(self::key())['label'];
    }
}

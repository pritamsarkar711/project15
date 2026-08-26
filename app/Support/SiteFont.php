<?php

namespace App\Support;

use App\Models\Setting;

/**
 * Read the active site-wide font family. Falls back to "work-sans"
 * if no setting is configured.
 */
class SiteFont
{
    public static function key(): string
    {
        try {
            $value = Setting::get('site_font_family', 'work-sans');
            if (! is_string($value) || $value === '') {
                return 'work-sans';
            }

            return $value;
        } catch (\Throwable $e) {
            return 'work-sans';
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

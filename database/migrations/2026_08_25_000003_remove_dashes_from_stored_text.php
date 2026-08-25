<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Remove em dashes from stored user-facing text settings so the whole site
 * reads with plain punctuation (the owner asked for a site free of dashes
 * and asterisks). Only affects the specific text settings that previously
 * shipped with an em dash as their default; ads.txt / robots.txt / llms.txt
 * technical content is deliberately untouched (their syntax needs * and
 * other characters).
 */
return new class extends Migration
{
    public function up(): void
    {
        $textKeys = ['site_tagline', 'site_description', 'footer_copyright', 'hero_subtitle'];

        foreach ($textKeys as $key) {
            try {
                DB::table('settings')
                    ->where('key', $key)
                    ->where('value', 'like', '%'.chr(0xE2).chr(0x80).chr(0x94).'%') // UTF-8 em dash
                    ->update(['value' => DB::raw("REPLACE(value, ' ".chr(0xE2).chr(0x80).chr(0x94)." ', ', ')")]);
            } catch (\Throwable $e) {
                // Non-fatal: cosmetic cleanup only.
            }
        }

        // The old hero subtitle default read awkwardly after a generic
        // replace; give it a clean sentence when it still matches the old
        // default exactly.
        try {
            DB::table('settings')
                ->where('key', 'hero_subtitle')
                ->where('value', 'Tech, health, finance, travel and more, one calm place to read.')
                ->update(['value' => 'Tech, health, finance, travel and more, all in one calm place to read.']);
            DB::table('settings')
                ->where('key', 'hero_subtitle')
                ->where('value', 'Tech, health, finance, travel and more — one calm place to read.')
                ->update(['value' => 'Tech, health, finance, travel and more, all in one calm place to read.']);
        } catch (\Throwable $e) {
            // Non-fatal.
        }
    }

    public function down(): void
    {
        // Irreversible text cleanup; restoring the old punctuation is not
        // something anyone wants.
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Replace category icon keys that were removed from the allowed set
 * (decorative "flashy" glyphs: sparkles, shield) with calm, professional
 * Lucide equivalents so no live row ever renders a broken/fallback icon:
 *
 *   sparkles -> sun        (same precedent as the 2026_08_23 legacy map)
 *   shield   -> flag       (closest calm "insignia/standard" glyph)
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('categories', 'icon')) {
            return;
        }

        $map = [
            'sparkles' => 'sun',
            'shield'   => 'flag',
            'heart'    => 'heart-pulse',
        ];

        foreach ($map as $old => $new) {
            DB::table('categories')->where('icon', $old)->update(['icon' => $new]);
        }
    }

    public function down(): void
    {
        // Data normalisation is not reversible; intentionally empty.
    }
};

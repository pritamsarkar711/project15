<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * The original seeder stored a bare "2026-27, All Rights Reserved" string as
 * the footer copyright. That renders without a copyright symbol or site name
 * and looks broken on the live site.
 *
 * This migration upgrades any legacy bare value to the new default. The year
 * is expressed with the {year} token so the footer always shows the current
 * year automatically (the footer partial replaces the token at render time).
 */
return new class extends Migration
{
    public function up(): void
    {
        $default = '© {year} Huvanti. All Rights Reserved.';

        $pattern = '/^(?:\d{4}(?:\s*[-\/]\s*\d{2,4})?)?\s*,?\s*all rights reserved\.?\s*-?\s*$|^all rights reserved\.?\s*-?\s*$|^$/i';

        $rows = DB::table('settings')->where('key', 'footer_copyright')->get(['id', 'value']);

        foreach ($rows as $row) {
            $value = trim((string) $row->value);
            if ($value === '' || preg_match($pattern, $value)) {
                DB::table('settings')->where('id', $row->id)->update(['value' => $default]);
            }
        }
    }

    public function down(): void
    {
        // Restore the original seeded value only if it still holds the default
        // introduced by this migration; never clobber a custom admin value.
        DB::table('settings')
            ->where('key', 'footer_copyright')
            ->where('value', '© {year} Huvanti. All Rights Reserved.')
            ->update(['value' => '2026-27, All Rights Reserved']);
    }
};

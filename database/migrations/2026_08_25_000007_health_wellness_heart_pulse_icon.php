<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Round 6: switch the Health & Wellness category icon to the heart with a
 * pulse line, which reads instantly as "health" at every size.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('categories')
            ->where('slug', 'health-wellness')
            ->where('icon', 'activity')
            ->update(['icon' => 'heart-pulse']);
    }

    public function down(): void
    {
        DB::table('categories')
            ->where('slug', 'health-wellness')
            ->where('icon', 'heart-pulse')
            ->update(['icon' => 'activity']);
    }
};

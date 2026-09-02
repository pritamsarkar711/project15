<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Adds the author's personal dashboard font to the users table.
 *
 * Every author can pick a font family for their OWN author dashboard.
 * The choice affects only that author's panel: the public site, the admin
 * panel and other authors keep the site wide font.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('panel_font', 50)->nullable()->after('niche');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('panel_font');
        });
    }
};

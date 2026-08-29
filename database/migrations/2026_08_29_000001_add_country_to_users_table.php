<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Adds the `country` field to users (ISO 3166-1 alpha-2 code, e.g. "BD").
     *
     * Authors and readers pick their country in the profile section of the
     * author dashboard; the flag icon + country name then show up on their
     * public author profile, on the post byline and in the author box.
     * Nullable + no index: purely optional profile decoration.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('country', 2)->nullable()->after('portfolio_url');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('country');
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Adds the author's primary niche to the users table.
     *
     * Every author picks ONE niche (an active post category) as their main
     * topic or area of expertise. It is shown on the public author profile
     * so readers instantly know what the author writes about.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Stores the category slug (e.g. "technology"). Nullable because
            // existing authors have not picked one yet.
            $table->string('niche', 100)->nullable()->after('role_title');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('niche');
        });
    }
};

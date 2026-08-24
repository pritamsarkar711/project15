<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('reader')->after('email');
            $table->text('bio')->nullable()->after('role');
            $table->string('avatar')->nullable()->after('bio');
            $table->boolean('two_factor_enabled')->default(false)->after('avatar');
            $table->string('two_factor_secret')->nullable()->after('two_factor_enabled');
            $table->string('two_factor_recovery_codes')->nullable()->after('two_factor_secret');
            $table->string('theme_preference')->default('light')->after('two_factor_recovery_codes');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role','bio','avatar','two_factor_enabled','two_factor_secret','two_factor_recovery_codes','theme_preference']);
        });
    }
};

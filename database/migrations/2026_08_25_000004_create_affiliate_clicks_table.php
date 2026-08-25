<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Affiliate / external link click tracking.
 *
 * When a visitor clicks an external link inside a post flagged as affiliate,
 * the click is recorded here. Authors then see total clicks and the click
 * rate (clicks divided by post views) on their Revenue page.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('affiliate_clicks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('url_hash', 64)->nullable();   // hashed destination, no personal data stored
            $table->string('ip_hash', 64)->nullable();    // hashed for simple uniqueness, not raw IP
            $table->timestamps();
            $table->index(['post_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('affiliate_clicks');
    }
};

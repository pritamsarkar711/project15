<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Post reactions: the "Did you like this post?" like / dislike buttons shown
 * after the content and FAQ section of every published post. One reaction
 * per user per post (a second click on the same button removes it; clicking
 * the other side switches it). Totals are shown on the author profile page.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('post_reactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('reaction', 10); // 'like' or 'dislike'
            $table->timestamps();
            $table->unique(['post_id', 'user_id']);
            $table->index(['reaction']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('post_reactions');
    }
};

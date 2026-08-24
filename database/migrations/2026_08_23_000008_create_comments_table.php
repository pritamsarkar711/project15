<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('email');
            $table->text('content');
            $table->enum('status', ['pending','approved','rejected','spam'])->default('pending');
            $table->foreignId('parent_id')->nullable()->constrained('comments')->cascadeOnDelete();
            $table->string('ip_address')->nullable();
            $table->timestamps();
            $table->index(['post_id','status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('comments');
    }
};

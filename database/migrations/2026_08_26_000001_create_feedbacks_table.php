<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('feedbacks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('overall_experience', 30)->nullable();
            $table->string('profile_ease', 30)->nullable();
            $table->string('publishing_ease', 30)->nullable();
            $table->text('bug_report')->nullable();
            $table->text('what_you_like')->nullable();
            $table->text('what_to_improve')->nullable();
            $table->text('feature_request')->nullable();
            $table->text('additional_comment')->nullable();
            $table->timestamps();
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('feedbacks');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Outfit of the Week: one entry per player per week, and votes on entries.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('website_outfits', function (Blueprint $table): void {
            $table->id();
            $table->playerId('user_id');
            $table->date('week');
            $table->string('look', 255);
            $table->string('gender', 1)->default('M');
            $table->unsignedInteger('votes')->default(0);
            $table->boolean('winner')->default(false);
            $table->timestamps();

            $table->unique(['user_id', 'week']);
            $table->index(['week', 'votes']);
            $table->foreignPlayer('user_id')->cascadeOnDelete();
        });

        Schema::create('website_outfit_votes', function (Blueprint $table): void {
            $table->unsignedBigInteger('outfit_id');
            $table->playerId('user_id');
            $table->timestamp('created_at')->nullable();

            $table->primary(['outfit_id', 'user_id']);
            $table->foreignPlayer('user_id')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('website_outfit_votes');
        Schema::dropIfExists('website_outfits');
    }
};

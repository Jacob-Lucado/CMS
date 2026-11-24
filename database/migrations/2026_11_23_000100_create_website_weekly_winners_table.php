<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The player who topped each weekly leaderboard, so a week is only closed once.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('website_weekly_winners', function (Blueprint $table): void {
            $table->id();
            $table->date('week_start');
            $table->string('board', 20);
            $table->playerId('user_id');
            $table->unsignedInteger('score');
            $table->string('badge', 50)->nullable();
            $table->timestamps();

            $table->unique(['week_start', 'board']);
            $table->foreignPlayer('user_id')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('website_weekly_winners');
    }
};

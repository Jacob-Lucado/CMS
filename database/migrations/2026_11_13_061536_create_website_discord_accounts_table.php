<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * One Discord account per player and one player per Discord account. The row goes with the player.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('website_discord_accounts', function (Blueprint $table): void {
            $table->id();
            $table->playerId('user_id')->unique();
            $table->string('discord_id', 32)->unique();
            $table->string('discord_username', 64);
            $table->string('discord_avatar', 64)->nullable();
            $table->timestamps();

            $table->foreignPlayer('user_id')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('website_discord_accounts');
    }
};

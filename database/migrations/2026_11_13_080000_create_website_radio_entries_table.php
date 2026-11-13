<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The hotel radio: SoundCloud tracks and live stations, added by DJs or sent in by players.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('website_radio_entries', function (Blueprint $table): void {
            $table->id();
            $table->string('kind', 16);
            $table->string('status', 16)->default('pending')->index();
            $table->string('title', 120);
            $table->string('subtitle', 120)->default('');
            $table->string('url', 300);
            $table->string('stream', 300)->unique();
            $table->string('art', 300)->nullable();
            $table->playerId('user_id')->nullable();
            $table->playerId('reviewed_by')->nullable();
            $table->timestamps();

            $table->foreignPlayer('user_id')->nullOnDelete();
            $table->foreignPlayer('reviewed_by')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('website_radio_entries');
    }
};

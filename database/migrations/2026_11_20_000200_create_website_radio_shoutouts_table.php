<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Short messages players send to the radio. The newest show on the radio page and in the mini player.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('website_radio_shoutouts', function (Blueprint $table): void {
            $table->id();
            $table->playerId('user_id')->nullable();
            $table->string('message', 140);
            $table->timestamps();

            $table->foreignPlayer('user_id')->nullOnDelete();
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('website_radio_shoutouts');
    }
};

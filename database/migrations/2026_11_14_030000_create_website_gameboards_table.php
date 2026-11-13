<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Gameboards link a highscore furni in a room to the website.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('website_gameboards', function (Blueprint $table): void {
            $table->id();
            $table->string('name', 80);
            $table->string('description', 255)->nullable();
            $table->unsignedBigInteger('item_id');
            $table->unsignedInteger('room_id')->nullable();
            $table->integer('order_num')->default(0);
            $table->boolean('enabled')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('website_gameboards');
    }
};

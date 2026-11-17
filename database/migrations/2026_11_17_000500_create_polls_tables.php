<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('website_polls', function (Blueprint $table): void {
            $table->id();
            $table->string('question');
            $table->text('options');
            $table->boolean('published')->default(true);
            $table->timestamp('closes_at')->nullable();
            $table->timestamps();
        });

        Schema::create('website_poll_votes', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('poll_id');
            $table->unsignedInteger('user_id');
            $table->unsignedSmallInteger('option_index');
            $table->timestamps();
            $table->unique(['poll_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('website_poll_votes');
        Schema::dropIfExists('website_polls');
    }
};

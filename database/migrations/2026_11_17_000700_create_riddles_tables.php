<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('website_riddles', function (Blueprint $table): void {
            $table->id();
            $table->string('question');
            $table->string('hint')->nullable();
            $table->string('answer');
            $table->unsignedInteger('reward_credits')->default(25);
            $table->date('active_on')->index();
            $table->boolean('published')->default(true);
            $table->timestamps();
        });

        Schema::create('website_riddle_solves', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('riddle_id');
            $table->unsignedInteger('user_id');
            $table->timestamps();
            $table->unique(['riddle_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('website_riddle_solves');
        Schema::dropIfExists('website_riddles');
    }
};

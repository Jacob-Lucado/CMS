<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('website_suggestions', function (Blueprint $table): void {
            $table->id();
            $table->unsignedInteger('user_id')->index();
            $table->string('title', 120);
            $table->text('body');
            $table->string('status', 16)->default('open')->index();
            $table->text('staff_reply')->nullable();
            $table->timestamps();
        });

        Schema::create('website_suggestion_votes', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('suggestion_id');
            $table->unsignedInteger('user_id');
            $table->timestamps();
            $table->unique(['suggestion_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('website_suggestion_votes');
        Schema::dropIfExists('website_suggestions');
    }
};

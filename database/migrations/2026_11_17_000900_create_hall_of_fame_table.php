<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('website_hall_of_fame', function (Blueprint $table): void {
            $table->id();
            $table->unsignedInteger('user_id')->index();
            $table->string('title', 80);
            $table->string('note')->nullable();
            $table->date('awarded_on');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('website_hall_of_fame');
    }
};

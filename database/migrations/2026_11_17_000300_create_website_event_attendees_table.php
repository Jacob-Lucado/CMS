<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('website_event_attendees', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('website_event_id');
            $table->unsignedBigInteger('user_id');
            $table->timestamps();

            $table->unique(['website_event_id', 'user_id']);
            $table->foreign('website_event_id')->references('id')->on('website_events')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('website_event_attendees');
    }
};

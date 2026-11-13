<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Stripe Checkout top-ups. A payment is credited once, when Stripe says it is paid.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('website_stripe_payments', function (Blueprint $table): void {
            $table->id();
            $table->playerId('user_id');
            $table->string('session_id', 255)->unique();
            $table->unsignedInteger('amount');
            $table->string('currency', 3);
            $table->string('status', 20)->default('open');
            $table->string('payment_intent', 255)->nullable();
            $table->timestamp('credited_at')->nullable();
            $table->timestamps();

            $table->foreignPlayer('user_id')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('website_stripe_payments');
    }
};

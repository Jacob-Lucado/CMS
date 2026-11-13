<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Square top-ups. A payment is credited once, when Square shows its order as paid.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('website_stripe_payments');

        Schema::create('website_square_payments', function (Blueprint $table): void {
            $table->id();
            $table->playerId('user_id');
            $table->uuid('reference')->unique();
            $table->string('order_id', 255)->nullable()->unique();
            $table->string('payment_link_id', 255)->nullable();
            $table->unsignedInteger('amount');
            $table->string('currency', 3);
            $table->string('status', 20)->default('open');
            $table->string('payment_id', 255)->nullable();
            $table->timestamp('credited_at')->nullable();
            $table->timestamps();

            $table->foreignPlayer('user_id')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('website_square_payments');
    }
};

<?php

use App\Models\Miscellaneous\WebsiteSetting;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('website_daily_rewards', function (Blueprint $table): void {
            $table->id();
            $table->unsignedInteger('user_id')->unique();
            $table->unsignedInteger('streak')->default(0);
            $table->unsignedInteger('best_streak')->default(0);
            $table->unsignedInteger('total_claims')->default(0);
            $table->date('last_claimed_on')->nullable();
            $table->timestamps();
        });

        Schema::create('website_featured_rooms', function (Blueprint $table): void {
            $table->id();
            $table->unsignedInteger('room_id');
            $table->string('note')->nullable();
            $table->boolean('room_of_the_week')->default(false);
            $table->unsignedSmallInteger('position')->default(0);
            $table->timestamps();
        });

        Schema::table('website_events', function (Blueprint $table): void {
            $table->timestamp('reminded_at')->nullable()->after('published');
        });

        Schema::create('website_rare_value_histories', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('rare_value_id')->index();
            $table->string('credit_value')->nullable();
            $table->string('currency_value')->nullable();
            $table->tinyInteger('currency_type')->default(0);
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('website_ban_appeals', function (Blueprint $table): void {
            $table->id();
            $table->unsignedInteger('user_id')->nullable();
            $table->string('username', 64);
            $table->text('message');
            $table->string('status', 16)->default('pending')->index();
            $table->text('staff_note')->nullable();
            $table->unsignedInteger('decided_by')->nullable();
            $table->timestamp('decided_at')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();
        });

        WebsiteSetting::query()->firstOrCreate(['key' => 'daily_reward_credits'], [
            'value' => '50',
            'comment' => 'Credits a player gets for claiming the daily reward',
        ]);
        WebsiteSetting::query()->firstOrCreate(['key' => 'daily_reward_streak_bonus'], [
            'value' => '10',
            'comment' => 'Extra credits added for each day in a streak, up to six extra days',
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('website_ban_appeals');
        Schema::dropIfExists('website_rare_value_histories');
        Schema::table('website_events', function (Blueprint $table): void {
            $table->dropColumn('reminded_at');
        });
        Schema::dropIfExists('website_featured_rooms');
        Schema::dropIfExists('website_daily_rewards');
        WebsiteSetting::query()->whereIn('key', ['daily_reward_credits', 'daily_reward_streak_bonus'])->delete();
    }
};

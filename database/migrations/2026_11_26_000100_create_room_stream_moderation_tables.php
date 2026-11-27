<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Banned :music links and the strikes players earn for playing them. The emulator owns both
 * tables, so this only fills the gap on a site that runs before the emulator update.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('room_stream_bans')) {
            Schema::create('room_stream_bans', function (Blueprint $table): void {
                $table->unsignedInteger('id', true);
                $table->string('ban_key', 200)->unique('uq_room_stream_bans_key');
                $table->string('url', 500);
                $table->string('reason', 200)->default('');
                $table->string('banned_by', 50);
                $table->timestamp('created_at')->useCurrent();
            });
        }

        if (! Schema::hasTable('room_stream_strikes')) {
            Schema::create('room_stream_strikes', function (Blueprint $table): void {
                $table->integer('user_id')->primary();
                $table->unsignedInteger('strikes')->default(0);
                $table->boolean('restricted')->default(false);
                $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
            });
        }
    }

    public function down(): void
    {
        // The emulator owns these tables.
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Songs started in rooms with :music. The emulator writes the rows and creates the table
 * itself, so this only fills the gap on a site that runs before the emulator update.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('room_stream_plays')) {
            return;
        }

        Schema::create('room_stream_plays', function (Blueprint $table): void {
            $table->unsignedInteger('id', true);
            $table->unsignedInteger('room_id');
            $table->string('set_by', 50);
            $table->string('kind', 16);
            $table->string('url', 500);
            $table->timestamp('played_at')->useCurrent();

            $table->index('played_at', 'idx_room_stream_plays_played_at');
            $table->index('set_by', 'idx_room_stream_plays_set_by');
        });
    }

    public function down(): void
    {
        // The emulator owns this table.
    }
};

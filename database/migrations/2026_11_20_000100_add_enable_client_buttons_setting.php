<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('website_settings')->updateOrInsert(
            ['key' => 'enable_client_buttons'],
            [
                'value' => '1',
                'comment' => 'Shows the home, reload and fullscreen buttons on the client. Home keeps the game running and Return to hotel brings it back.',
            ],
        );
    }

    public function down(): void
    {
        DB::table('website_settings')->where('key', 'enable_client_buttons')->delete();
    }
};

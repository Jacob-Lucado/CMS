<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('website_settings')->updateOrInsert(
            ['key' => 'weekly_music_badge'],
            ['value' => '', 'comment' => 'Badge code given each Monday to the player who played the most songs last week. Leave empty to give no badge.'],
        );

        DB::table('website_settings')->updateOrInsert(
            ['key' => 'weekly_kudos_badge'],
            ['value' => '', 'comment' => 'Badge code given each Monday to the player who got the most kudos last week. Leave empty to give no badge.'],
        );
    }

    public function down(): void
    {
        DB::table('website_settings')->whereIn('key', ['weekly_music_badge', 'weekly_kudos_badge'])->delete();
    }
};

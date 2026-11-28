<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('website_settings')->updateOrInsert(
            ['key' => 'theme'],
            ['value' => 'lucado', 'comment' => 'Specifies the active CMS theme (lucadoos, dusk, atom, mercury, revpro, lucado)'],
        );
    }

    public function down(): void
    {
        DB::table('website_settings')->where('key', 'theme')->where('value', 'lucado')->update(['value' => 'lucadoos']);
    }
};

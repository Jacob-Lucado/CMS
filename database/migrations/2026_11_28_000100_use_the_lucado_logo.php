<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const ATOM_LOGO = '/assets/images/kasja_atomlogo.png';

    private const LUCADO_LOGO = '/assets/images/lucado-logo.png';

    public function up(): void
    {
        $current = DB::table('website_settings')->where('key', 'cms_logo')->value('value');

        if ($current === null) {
            return;
        }

        // A logo that was set by hand and still exists stays as it is.
        $isOwnFile = is_string($current) && $current !== self::ATOM_LOGO && is_file(public_path(ltrim(parse_url($current, PHP_URL_PATH) ?: '', '/')));

        if (! $isOwnFile) {
            DB::table('website_settings')->where('key', 'cms_logo')->update(['value' => self::LUCADO_LOGO]);
        }
    }

    public function down(): void
    {
        DB::table('website_settings')->where('key', 'cms_logo')->where('value', self::LUCADO_LOGO)->update(['value' => self::ATOM_LOGO]);
    }
};

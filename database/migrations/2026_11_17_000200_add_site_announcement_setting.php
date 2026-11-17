<?php

use App\Models\Miscellaneous\WebsiteSetting;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        WebsiteSetting::query()->firstOrCreate(['key' => 'site_announcement'], [
            'value' => '',
            'comment' => 'A short message shown at the top of every page, for news or maintenance (leave empty to hide it)',
        ]);
    }

    public function down(): void
    {
        WebsiteSetting::query()->where('key', 'site_announcement')->delete();
    }
};

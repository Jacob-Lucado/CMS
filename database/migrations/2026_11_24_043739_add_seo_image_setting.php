<?php

use App\Models\Miscellaneous\WebsiteSetting;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        WebsiteSetting::query()->firstOrCreate(['key' => 'seo_image'], [
            'value' => '',
            'comment' => 'The image shown when a page is shared on social media and chat apps (leave empty to use the bundled hotel image)',
        ]);
    }

    public function down(): void
    {
        WebsiteSetting::query()->where('key', 'seo_image')->delete();
    }
};

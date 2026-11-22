<?php

use App\Models\WebsiteHousekeepingPermission;
use App\Support\PermissionRanks;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /** @var list<array{permission: string, min_rank: int, description: string}> */
    private array $permissions = [
        [
            'permission' => 'manage_social',
            'min_rank' => 6,
            'description' => 'The minimum rank required before being able to review reported posts, send website notifications and manage events',
        ],
        [
            'permission' => 'manage_website_content',
            'min_rank' => 6,
            'description' => 'The minimum rank required before being able to edit the site rules and rare values',
        ],
        [
            'permission' => 'manage_security',
            'min_rank' => 7,
            'description' => 'The minimum rank required before being able to manage beta codes and the IP blacklist and whitelist',
        ],
    ];

    public function up(): void
    {
        foreach ($this->permissions as $permission) {
            WebsiteHousekeepingPermission::query()->firstOrCreate(
                ['permission' => $permission['permission']],
                [...$permission, 'min_rank' => PermissionRanks::scale($permission['min_rank'])],
            );
        }
    }

    public function down(): void
    {
        WebsiteHousekeepingPermission::query()
            ->whereIn('permission', ['manage_website_content', 'manage_security'])
            ->delete();
    }
};

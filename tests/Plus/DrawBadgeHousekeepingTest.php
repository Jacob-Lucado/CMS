<?php

use App\Filament\Resources\Atom\WebsiteDrawBadges\WebsiteDrawBadgeResource;
use App\Models\User;
use App\Models\WebsiteDrawBadge;
use App\Models\WebsiteHousekeepingPermission;
use App\Services\HousekeepingPermissionsService;
use Illuminate\Support\Facades\DB;

beforeEach(function () {
    installHotel();
    DB::table('roles')->insert(['id' => 1, 'slug' => 'default', 'name' => 'Default', 'description' => '', 'weight' => 1, 'security_level' => 1, 'badge_code' => '', 'is_staff' => false, 'is_hidden' => false, 'created_at' => now(), 'updated_at' => now()]);
    DB::table('roles')->insert(['id' => 2, 'slug' => 'admin', 'name' => 'Admin', 'description' => '', 'weight' => 2, 'security_level' => 7, 'badge_code' => '', 'is_staff' => true, 'is_hidden' => false, 'created_at' => now(), 'updated_at' => now()]);
    setSetting('start_duckets', '0');
    setSetting('start_diamonds', '0');
    setSetting('start_points', '0');
});

test('staff can open the drawn badges list in housekeeping', function () {
    foreach (['can_access_housekeeping', 'manage_badges'] as $permission) {
        WebsiteHousekeepingPermission::query()->updateOrCreate(['permission' => $permission], ['min_rank' => 6, 'description' => $permission]);
    }
    app(HousekeepingPermissionsService::class)->refresh();

    $staff = User::factory()->create(['rank' => 7]);
    $drawer = User::factory()->create(['username' => 'Drawer']);
    WebsiteDrawBadge::query()->create(['user_id' => $drawer->id, 'badge_path' => '/tmp/1.gif', 'badge_url' => '/badges/1.gif', 'badge_name' => 'Cool', 'badge_desc' => 'A cool badge']);

    $this->actingAs($staff)->get('/housekeeping/draw-badges')->assertOk()->assertSee('Drawer');
});

test('the menu counts drawn badges that wait for approval', function () {
    $drawer = User::factory()->create();
    $draw = fn (bool $published) => WebsiteDrawBadge::query()->create(['user_id' => $drawer->id, 'badge_path' => '/tmp/x.gif', 'badge_url' => '/badges/x.gif', 'badge_name' => 'Cool', 'badge_desc' => 'A cool badge', 'published' => $published]);

    expect(WebsiteDrawBadgeResource::getNavigationBadge())->toBeNull();

    $draw(false);
    $draw(false);
    $draw(true);

    expect(WebsiteDrawBadgeResource::getNavigationBadge())->toBe('2');
});

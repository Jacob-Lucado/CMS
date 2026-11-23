<?php

use App\Filament\Resources\Hotel\MusicPlays\Pages\ManageMusicPlays;
use App\Filament\Widgets\MusicOverview;
use App\Filament\Widgets\WelcomeBanner;
use App\Models\RoomStreamPlay;
use App\Models\User;
use App\Models\WebsiteHousekeepingPermission;
use App\Services\HousekeepingPermissionsService;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;

beforeEach(function () {
    foreach (['can_access_housekeeping', 'manage_commandlogs', 'edit_user'] as $permission) {
        WebsiteHousekeepingPermission::query()->updateOrCreate(['permission' => $permission], ['min_rank' => 1, 'description' => $permission]);
    }
    app(HousekeepingPermissionsService::class)->refresh();
    Filament::setCurrentPanel(Filament::getPanel('housekeeping'));
    DB::table('roles')->insert(['id' => 9, 'slug' => 'owner', 'name' => 'Owner', 'description' => '', 'weight' => 90, 'security_level' => 7, 'badge_code' => '', 'is_staff' => true, 'is_hidden' => false, 'created_at' => now(), 'updated_at' => now()]);
    DB::table('roles')->insert(['id' => 1, 'slug' => 'default', 'name' => 'User', 'description' => '', 'weight' => 0, 'security_level' => 1, 'badge_code' => '', 'is_staff' => false, 'is_hidden' => false, 'created_at' => now(), 'updated_at' => now()]);
    Cache::flush();
});

function logPlay(string $by, string $url, string $when = 'now', string $kind = 'youtube'): RoomStreamPlay
{
    return RoomStreamPlay::query()->create(['room_id' => 4, 'set_by' => $by, 'kind' => $kind, 'url' => $url, 'played_at' => now()->modify($when)]);
}

test('staff see every song, newest first, and can filter them', function () {
    $staff = User::factory()->create(['rank' => 7]);
    $old = logPlay('Old', 'https://example.com/old', '-3 days');
    $new = logPlay('New', 'https://example.com/new', '-1 minute', 'soundcloud');

    $this->actingAs($staff)->get('/housekeeping/logs/music')->assertOk();

    Livewire::test(ManageMusicPlays::class)
        ->assertCanSeeTableRecords([$new, $old], inOrder: true)
        ->filterTable('today')
        ->assertCanSeeTableRecords([$new])
        ->assertCanNotSeeTableRecords([$old])
        ->removeTableFilter('today')
        ->filterTable('kind', 'youtube')
        ->assertCanSeeTableRecords([$old])
        ->assertCanNotSeeTableRecords([$new])
        ->removeTableFilter('kind')
        ->searchTable('New')
        ->assertCanSeeTableRecords([$new])
        ->assertCanNotSeeTableRecords([$old]);
});

test('the music log is read only', function () {
    $this->actingAs(User::factory()->create(['rank' => 7]));

    expect(ManageMusicPlays::getResource()::canCreate())->toBeFalse();
});

test('players without the permission cannot open the music log', function () {
    WebsiteHousekeepingPermission::query()->where('permission', 'manage_commandlogs')->update(['min_rank' => 9]);
    app(HousekeepingPermissionsService::class)->refresh();

    $this->actingAs(User::factory()->create(['rank' => 7]))->get('/housekeeping/logs/music')->assertForbidden();
});

test('the dashboard music card counts songs and names the top one', function () {
    $this->actingAs(User::factory()->create(['rank' => 7]));
    logPlay('A', 'https://www.youtube.com/watch?v=hit');
    logPlay('B', 'https://www.youtube.com/watch?v=hit');
    logPlay('B', 'https://example.com/other');
    logPlay('C', 'https://example.com/old', '-10 days');

    expect(MusicOverview::canView())->toBeTrue();

    Livewire::test(MusicOverview::class)
        ->assertSee('Songs today')
        ->assertSee('youtube.com/watch?v=hit')
        ->assertSee('2 plays')
        ->assertSee('DJs this week');
});

test('the dashboard greets staff and links to the tools they can use', function () {
    $staff = User::factory()->create(['rank' => 7, 'username' => 'Jacob']);
    User::factory()->count(2)->create(['online' => true]);
    Cache::flush();

    $this->actingAs($staff)->get('/housekeeping')->assertOk();

    Livewire::test(WelcomeBanner::class)
        ->assertSee('Jacob')
        ->assertSee('online right now')
        ->assertSee('Find a player')
        ->assertSee('Room music');
});

test('the banner counts only players who are online', function () {
    $this->actingAs(User::factory()->create(['rank' => 7, 'online' => false]));
    User::factory()->count(2)->create(['online' => true]);
    User::factory()->create(['online' => false]);
    Cache::flush();

    expect(Livewire::test(WelcomeBanner::class)->instance()->online())->toBe(2);
});

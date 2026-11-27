<?php

use App\Filament\Resources\Hotel\MusicBans\Pages\ManageMusicBans;
use App\Filament\Resources\Hotel\MusicStrikes\Pages\ManageMusicStrikes;
use App\Models\RoomStreamBan;
use App\Models\RoomStreamStrike;
use App\Models\User;
use App\Models\WebsiteHousekeepingPermission;
use App\Services\HousekeepingPermissionsService;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;

beforeEach(function () {
    foreach (['can_access_housekeeping', 'manage_wordfilter'] as $permission) {
        WebsiteHousekeepingPermission::query()->updateOrCreate(['permission' => $permission], ['min_rank' => 1, 'description' => $permission]);
    }
    app(HousekeepingPermissionsService::class)->refresh();
    Filament::setCurrentPanel(Filament::getPanel('housekeeping'));
    DB::table('roles')->insert(['id' => 9, 'slug' => 'owner', 'name' => 'Owner', 'description' => '', 'weight' => 90, 'security_level' => 7, 'badge_code' => '', 'is_staff' => true, 'is_hidden' => false, 'created_at' => now(), 'updated_at' => now()]);
    DB::table('roles')->insert(['id' => 1, 'slug' => 'default', 'name' => 'User', 'description' => '', 'weight' => 0, 'security_level' => 1, 'badge_code' => '', 'is_staff' => false, 'is_hidden' => false, 'created_at' => now(), 'updated_at' => now()]);
    Cache::flush();
});

test('a link is banned by the same key the emulator uses', function (string $link, ?string $key) {
    expect(RoomStreamBan::keyFor($link))->toBe($key);
})->with([
    ['https://www.youtube.com/watch?v=dQw4w9WgXcQ', 'youtube:dQw4w9WgXcQ'],
    ['https://www.youtube.com/watch?v=dQw4w9WgXcQ&list=PLabcdefghijk123', 'youtube-list:PLabcdefghijk123'],
    ['https://www.youtube.com/playlist?list=PLabcdefghijk123', 'youtube-list:PLabcdefghijk123'],
    ['https://youtu.be/dQw4w9WgXcQ', 'youtube:dQw4w9WgXcQ'],
    ['https://soundcloud.com/Jacob-LLLL/sets/related-tracks-beyonce-crazy/?si=1', 'soundcloud:soundcloud.com/jacob-llll/sets/related-tracks-beyonce-crazy'],
    ['http://soundcloud.com/a/b', null],
    ['not a link', null],
]);

test('staff ban a link and unban it', function () {
    $staff = User::factory()->create(['rank' => 7]);
    $this->actingAs($staff);

    Livewire::test(ManageMusicBans::class)
        ->callAction(CreateAction::class, ['url' => 'https://soundcloud.com/a/bad', 'reason' => 'Loud'])
        ->assertHasNoActionErrors();

    $ban = RoomStreamBan::query()->firstOrFail();
    expect($ban->ban_key)->toBe('soundcloud:soundcloud.com/a/bad')
        ->and($ban->banned_by)->toBe($staff->username);

    Livewire::test(ManageMusicBans::class)
        ->callTableAction(DeleteAction::class, $ban);

    expect(RoomStreamBan::query()->count())->toBe(0);
});

test('staff lift a music strike', function () {
    $staff = User::factory()->create(['rank' => 7]);
    $player = User::factory()->create();
    $this->actingAs($staff);
    RoomStreamStrike::query()->create(['user_id' => $player->id, 'strikes' => 3, 'restricted' => true]);

    Livewire::test(ManageMusicStrikes::class)
        ->assertCanSeeTableRecords(RoomStreamStrike::all())
        ->callTableAction('lift', RoomStreamStrike::query()->firstOrFail());

    expect(RoomStreamStrike::query()->count())->toBe(0);
});

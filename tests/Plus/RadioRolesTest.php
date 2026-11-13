<?php

use App\Emulator\Contracts\RankRepository;
use App\Models\User;
use App\Services\Radio\RadioAccess;
use Illuminate\Support\Facades\DB;

beforeEach(function () {
    DB::table('roles')->insert([
        ['id' => 1, 'slug' => 'default', 'name' => 'User', 'description' => '', 'weight' => 0, 'security_level' => 1, 'badge_code' => '', 'is_staff' => false, 'is_hidden' => false, 'created_at' => now(), 'updated_at' => now()],
        ['id' => 13, 'slug' => 'events_staff', 'name' => 'Events Staff', 'description' => '', 'weight' => 10, 'security_level' => 1, 'badge_code' => '', 'is_staff' => false, 'is_hidden' => false, 'created_at' => now(), 'updated_at' => now()],
        ['id' => 9, 'slug' => 'owner', 'name' => 'Owner', 'description' => '', 'weight' => 90, 'security_level' => 7, 'badge_code' => '', 'is_staff' => true, 'is_hidden' => false, 'created_at' => now(), 'updated_at' => now()],
    ]);
});

test('any plus role can be made a DJ, even one that shares a rank with regular players', function () {
    $host = User::factory()->create(['rank' => 1]);
    $player = User::factory()->create(['rank' => 1]);
    DB::table('user_roles')->insert(['user_id' => $host->id, 'role_id' => 13, 'created_at' => now()]);
    $access = app(RadioAccess::class);

    expect(app(RankRepository::class)->roleOptions())->toBe([9 => 'Owner', 13 => 'Events Staff', 1 => 'User']);

    $access->configure([13], true);

    expect($access->isDj($host))->toBeTrue()
        ->and($access->isDj($player))->toBeFalse();

    DB::table('user_roles')->where('user_id', $host->id)->where('role_id', 13)->update(['expires_at' => now('UTC')->subMinute()->format('Y-m-d H:i:s.u')]);

    expect($access->isDj($host))->toBeFalse();
});

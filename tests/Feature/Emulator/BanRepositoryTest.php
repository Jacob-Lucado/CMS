<?php

use App\Emulator\Drivers\Arcturus\ArcturusBanRepository;
use App\Models\User;
use App\Models\User\Ban;

beforeEach(function () {
    installHotel();
});

test('arcturus resolves active ip and account bans', function () {
    $bans = new ArcturusBanRepository;
    $user = User::factory()->create();

    Ban::create([
        'user_id' => $user->id, 'ip' => '10.0.0.1', 'machine_id' => '', 'user_staff_id' => $user->id,
        'timestamp' => time(), 'ban_expire' => time() + 3600, 'ban_reason' => 'IP misuse', 'type' => 'ip',
    ]);
    Ban::create([
        'user_id' => $user->id, 'ip' => '', 'machine_id' => '', 'user_staff_id' => $user->id,
        'timestamp' => time(), 'ban_expire' => time() + 3600, 'ban_reason' => 'Account misuse', 'type' => 'account',
    ]);

    expect($bans->activeIpBan('10.0.0.1')?->ban_reason)->toBe('IP misuse')
        ->and($bans->activeIpBan('10.0.0.2'))->toBeNull()
        ->and($bans->activeAccountBan($user)?->ban_reason)->toBe('Account misuse');
});

test('arcturus ignores expired bans', function () {
    $bans = new ArcturusBanRepository;
    $user = User::factory()->create();

    Ban::create([
        'user_id' => $user->id, 'ip' => '10.0.0.1', 'machine_id' => '', 'user_staff_id' => $user->id,
        'timestamp' => time(), 'ban_expire' => time() - 10, 'ban_reason' => 'Old', 'type' => 'ip',
    ]);

    expect($bans->activeIpBan('10.0.0.1'))->toBeNull();
});

<?php

use App\Emulator\Contracts\PlayerSettingsRepository;
use App\Models\User;
use Illuminate\Support\Facades\DB;

beforeEach(function () {
    installHotel();
    setSetting('start_duckets', '0');
    setSetting('start_diamonds', '0');
    setSetting('start_points', '0');
    setSetting('give_hc_on_register', '0');
});

test('arcturus owns name change settings through its repository', function () {
    $user = User::factory()->create();
    $settings = app(PlayerSettingsRepository::class);

    expect(DB::table('users_settings')->where('user_id', $user->id)->exists())->toBeTrue()
        ->and($settings->canChangeName($user))->toBeFalse();

    $settings->setCanChangeName($user, true);

    expect($settings->canChangeName($user))->toBeTrue();
});

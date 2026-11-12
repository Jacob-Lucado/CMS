<?php

use App\Emulator\Contracts\RoomRepository;
use App\Emulator\Data\RoomSummary;
use App\Models\User;
use Illuminate\Support\Facades\DB;

beforeEach(function () {
    installHotel();
    setSetting('start_duckets', '0');
    setSetting('start_diamonds', '0');
    setSetting('start_points', '0');
});

test('arcturus supplies rooms with their native state', function () {
    $user = User::factory()->create();
    $roomId = DB::table('rooms')->insertGetId([
        'owner_id' => $user->id,
        'name' => 'Locked room',
        'description' => 'Arcturus room',
        'state' => 'locked',
    ]);

    $rooms = app(RoomRepository::class)->forHome($user);

    expect(app(RoomRepository::class)->count())->toBeGreaterThanOrEqual(1)
        ->and($rooms)->toHaveCount(1)
        ->and($rooms->first())->toEqual(new RoomSummary(
            $roomId,
            'Locked room',
            'Arcturus room',
            'locked',
        ));
});

<?php

use App\Models\Game\PlusNavigatorPublic;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

beforeEach(function () {
    installHotel();
    DB::table('roles')->insert(['id' => 1, 'slug' => 'default', 'name' => 'Default', 'description' => '', 'weight' => 1, 'security_level' => 1, 'badge_code' => '', 'is_staff' => false, 'is_hidden' => false, 'created_at' => now(), 'updated_at' => now()]);
    setSetting('start_duckets', '0');
    setSetting('start_diamonds', '0');
    setSetting('start_points', '0');
});

function publicRoom(string $caption, int $order, bool $enabled = true, int $inside = 0): int
{
    $owner = User::query()->value('id') ?? User::factory()->create()->id;
    $room = makePlusRoom($owner, ['caption' => $caption, 'state' => 'open', 'users_now' => $inside, 'users_max' => 50]);
    PlusNavigatorPublic::query()->create(['room_id' => $room, 'caption' => $caption, 'description' => 'About ' . $caption, 'image_url' => '', 'order_num' => $order, 'enabled' => $enabled]);

    return $room;
}

test('the room browser lists public rooms in navigator order with their visitors', function () {
    publicRoom('Game Hub', 2, inside: 7);
    publicRoom('Welcome Lobby', 1, inside: 3);

    $this->actingAs(User::factory()->create())->get(route('rooms.index'))
        ->assertOk()
        ->assertSeeInOrder(['Public rooms', 'Welcome Lobby', '3 / 50', 'Game Hub', '7 / 50']);
});

test('a disabled public room is hidden', function () {
    publicRoom('Hidden Hall', 1, enabled: false);

    $this->actingAs(User::factory()->create())->get(route('rooms.index'))
        ->assertOk()
        ->assertDontSee('Public rooms');
});

test('the API lists the public rooms', function () {
    $room = publicRoom('Club NX', 1, inside: 4);

    $this->actingAs(User::factory()->create())->getJson('/api/v1/public-rooms')->assertOk()->assertJsonPath('data.0.id', $room)
        ->assertJsonPath('data.0.name', 'Club NX')
        ->assertJsonPath('data.0.users', 4)
        ->assertJsonPath('data.0.max', 50);
});

test('the API returns no public rooms when none are featured', function () {
    $this->actingAs(User::factory()->create())->getJson('/api/v1/public-rooms')->assertOk()->assertJsonPath('data', []);
});

test('the room browser lists the open rooms a player visited most recently', function () {
    Schema::dropIfExists('user_roomvisits');
    Schema::create('user_roomvisits', function ($table) {
        $table->increments('id');
        $table->unsignedInteger('room_id');
        $table->integer('user_id');
        $table->double('entry_timestamp')->nullable();
        $table->double('exit_timestamp')->nullable();
    });

    $user = User::factory()->create();
    $owner = User::factory()->create();
    $older = makePlusRoom($owner->id, ['caption' => 'Older Room', 'state' => 'open']);
    $newer = makePlusRoom($owner->id, ['caption' => 'Newer Room', 'state' => 'open']);
    $locked = makePlusRoom($owner->id, ['caption' => 'Locked Room', 'state' => 'locked']);
    foreach ([[$older, 100], [$newer, 300], [$locked, 400], [$newer, 200]] as [$room, $at]) {
        DB::table('user_roomvisits')->insert(['room_id' => $room, 'user_id' => $user->id, 'entry_timestamp' => $at]);
    }

    $this->actingAs($user)->get(route('rooms.index'))->assertOk()
        ->assertSeeInOrder(['Jump back in', 'Newer Room', 'Older Room'])
        ->assertDontSee('Locked Room');

    Schema::drop('user_roomvisits');
});

test('the API lists the signed-in player recent rooms', function () {
    Schema::dropIfExists('user_roomvisits');
    Schema::create('user_roomvisits', function ($table) {
        $table->increments('id');
        $table->unsignedInteger('room_id');
        $table->integer('user_id');
        $table->double('entry_timestamp')->nullable();
        $table->double('exit_timestamp')->nullable();
    });

    $user = User::factory()->create();
    $room = makePlusRoom(User::factory()->create()->id, ['caption' => 'Chill Room', 'state' => 'open']);
    DB::table('user_roomvisits')->insert(['room_id' => $room, 'user_id' => $user->id, 'entry_timestamp' => 100]);

    $this->actingAs($user)->getJson('/api/v1/me/recent-rooms')->assertOk()
        ->assertJsonPath('data.0.id', $room)
        ->assertJsonPath('data.0.name', 'Chill Room');

    Schema::drop('user_roomvisits');
});

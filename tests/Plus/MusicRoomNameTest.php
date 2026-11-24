<?php

use App\Models\RoomStreamPlay;
use App\Models\User;
use Illuminate\Support\Facades\DB;

beforeEach(function () {
    installHotel();
    DB::table('roles')->insert(['id' => 1, 'slug' => 'default', 'name' => 'Default', 'description' => '', 'weight' => 1, 'security_level' => 1, 'badge_code' => '', 'is_staff' => false, 'is_hidden' => false, 'created_at' => now(), 'updated_at' => now()]);
    setSetting('start_duckets', '0');
    setSetting('start_diamonds', '0');
    setSetting('start_points', '0');
});

test('recent songs name the room they were played in', function () {
    $owner = User::factory()->create();
    $room = makePlusRoom($owner->id, ['caption' => 'Game Hub']);
    RoomStreamPlay::query()->create(['room_id' => $room, 'set_by' => 'Alice', 'kind' => 'youtube', 'url' => 'https://www.youtube.com/watch?v=aaa', 'played_at' => now()]);
    RoomStreamPlay::query()->create(['room_id' => 999999, 'set_by' => 'Bob', 'kind' => 'youtube', 'url' => 'https://www.youtube.com/watch?v=bbb', 'played_at' => now()->subMinute()]);

    $this->actingAs($owner)->get(route('music.index'))->assertOk()->assertSee(route('music.room', $room))->assertSee('Game Hub');
    $this->actingAs($owner)->getJson('/api/v1/music')->assertOk()
        ->assertJsonPath('data.recent.0.room', 'Game Hub')
        ->assertJsonPath('data.recent.1.room', null);
});

test('a room music history page lists its plays and 404s for rooms with none', function () {
    $owner = User::factory()->create();
    $room = makePlusRoom($owner->id, ['caption' => 'Game Hub']);
    RoomStreamPlay::query()->create(['room_id' => $room, 'set_by' => 'Alice', 'kind' => 'youtube', 'url' => 'https://www.youtube.com/watch?v=aaa', 'played_at' => now()]);

    $this->actingAs($owner)->get(route('music.room', $room))->assertOk()
        ->assertSee('Music played in Game Hub')->assertSee('Alice');
    $this->actingAs($owner)->get(route('music.room', 424242))->assertNotFound();
});

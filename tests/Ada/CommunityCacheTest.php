<?php

use App\Models\Ada\AdaRole;
use App\Models\User;
use App\Services\Community\StaffService;

beforeEach(function () {
    installHotel();
});

test('rank cache invalidation follows the selected emulator model', function () {
    // Role 5 ships with Ada's seed data; rename it so the assertion is about
    // cache invalidation rather than about what the seed happens to contain.
    $role = AdaRole::query()->findOrFail(5);
    $role->update(['name' => 'Ada Moderator']);
    $viewer = User::factory()->create(['rank' => 1]);
    User::factory()->create(['rank' => 5]);
    $service = app(StaffService::class);

    expect($service->fetchStaffPositions($viewer)->firstWhere('id', 5)?->rank_name)
        ->toBe('Ada Moderator');

    $role->update(['name' => 'Ada Administrator']);

    expect($service->fetchStaffPositions($viewer)->firstWhere('id', 5)?->rank_name)
        ->toBe('Ada Administrator');
});

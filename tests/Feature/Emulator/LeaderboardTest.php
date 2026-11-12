<?php

use App\Emulator\Contracts\PlayerStatsRepository;
use App\Emulator\Data\Stat;
use App\Emulator\Drivers\Arcturus\ArcturusPlayerStatsRepository;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

beforeEach(function () {
    installHotel();
});

/** Arcturus half of the stats conformance suite; Ada's is in tests/Ada. */
dataset('stats drivers', [
    'arcturus' => [fn (): PlayerStatsRepository => new ArcturusPlayerStatsRepository],
]);

test('the stats leaderboard query runs against the schema', function (PlayerStatsRepository $stats) {
    // Smoke test: exercises the driver's column mapping without seeding the
    // emulator-owned stats table.
    expect($stats->topBy(Stat::OnlineTime, 5))->toBeInstanceOf(Collection::class);
})->with('stats drivers');

test('a ranked leaderboard loads its users in one query', function (PlayerStatsRepository $stats) {
    User::factory()->count(6)->create();

    DB::flushQueryLog();
    DB::enableQueryLog();

    $stats->topBy(Stat::AchievementScore, 6);
    $queries = count(DB::getQueryLog());

    DB::disableQueryLog();

    // One ranking query plus the user lookup, and on Ada the hydration pair.
    // Loading users one by one is the regression this guards against.
    expect($queries)->toBeLessThanOrEqual(4);
})->with('stats drivers');

test('the leaderboard page renders with the configured driver', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('leaderboard.index'))
        ->assertOk();
});

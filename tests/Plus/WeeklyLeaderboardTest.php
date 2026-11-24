<?php

use App\Contracts\Rcon;
use App\Filament\Widgets\WeeklyWinners;
use App\Models\Community\WeeklyWinner;
use App\Models\RoomStreamPlay;
use App\Models\Social\Kudos;
use App\Models\User;
use App\Services\Community\WeeklyLeaderboardService;
use App\Services\FakeRcon;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;

beforeEach(function () {
    installHotel();
    DB::table('roles')->insert(['id' => 1, 'slug' => 'default', 'name' => 'Default', 'description' => '', 'weight' => 1, 'security_level' => 1, 'badge_code' => '', 'is_staff' => false, 'is_hidden' => false, 'created_at' => now(), 'updated_at' => now()]);
    setSetting('start_duckets', '0');
    setSetting('start_diamonds', '0');
    setSetting('start_points', '0');
    $this->lastWeek = now()->subWeek()->startOfWeek();
});

function weeklyPlays(string $by, int $count, $when): void
{
    foreach (range(1, $count) as $i) {
        RoomStreamPlay::query()->create(['room_id' => 1, 'set_by' => $by, 'kind' => 'youtube', 'url' => "https://www.youtube.com/watch?v={$i}", 'played_at' => $when->copy()->addHours($i)]);
    }
}

function weeklyKudos(User $to, int $count, $when): void
{
    foreach (range(1, $count) as $i) {
        $from = User::factory()->create();
        Kudos::query()->create(['from_user_id' => $from->id, 'to_user_id' => $to->id, 'board' => 'lobby', 'given_on' => $when->copy()->addDays($i % 6)->toDateString()]);
    }
}

test('the weekly boards only count the current week', function () {
    $alice = User::factory()->create(['username' => 'Alice']);
    $bob = User::factory()->create(['username' => 'Bob']);
    weeklyPlays('Alice', 3, now()->startOfWeek());
    weeklyPlays('Bob', 5, now()->subWeek()->startOfWeek());
    weeklyKudos($bob, 2, now()->startOfWeek());

    $standings = app(WeeklyLeaderboardService::class);

    expect($standings->standings('music', $standings->weekStart())->map(fn ($e) => [$e->user->username, $e->value])->all())->toBe([['Alice', 3]])
        ->and($standings->standings('kudos', $standings->weekStart())->map(fn ($e) => [$e->user->username, $e->value])->all())->toBe([['Bob', 2]]);
});

test('closing a week records the winners and gives the badge once', function () {
    $alice = User::factory()->create(['username' => 'Alice']);
    $bob = User::factory()->create(['username' => 'Bob']);
    weeklyPlays('Alice', 4, $this->lastWeek);
    weeklyPlays('Bob', 2, $this->lastWeek);
    weeklyKudos($bob, 3, $this->lastWeek);
    setSetting('weekly_music_badge', 'ADM');

    $rcon = new FakeRcon;
    app()->instance(Rcon::class, $rcon);

    $this->artisan('leaderboard:award')->assertSuccessful();
    $this->artisan('leaderboard:award')->assertSuccessful();

    expect(WeeklyWinner::query()->count())->toBe(2);
    expect(WeeklyWinner::query()->where('board', 'music')->first())->user_id->toBe($alice->id)->score->toBe(4)->badge->toBe('ADM');
    expect(WeeklyWinner::query()->where('board', 'kudos')->first())->user_id->toBe($bob->id)->badge->toBeNull();
});

test('a week with no activity has no winners', function () {
    $this->artisan('leaderboard:award')->assertSuccessful();

    expect(WeeklyWinner::query()->count())->toBe(0);
});

test('the leaderboard page shows this week and last week\'s winners', function () {
    $alice = User::factory()->create(['username' => 'Alice']);
    weeklyPlays('Alice', 2, now()->startOfWeek());
    WeeklyWinner::query()->create(['week_start' => $this->lastWeek->toDateString(), 'board' => 'kudos', 'user_id' => $alice->id, 'score' => 6]);

    $this->actingAs(User::factory()->create())->get(route('leaderboard.index'))
        ->assertOk()
        ->assertSeeInOrder(['This week', 'Top DJs this week', 'Alice', '2 Songs played'])
        ->assertSee('Last week&#039;s winners', false)
        ->assertSeeInOrder(['Alice', 'Most kudos', '6']);
});

test('the API lists the weekly boards and last week\'s winners', function () {
    $alice = User::factory()->create(['username' => 'Alice']);
    weeklyPlays('Alice', 2, now()->startOfWeek());
    WeeklyWinner::query()->create(['week_start' => $this->lastWeek->toDateString(), 'board' => 'kudos', 'user_id' => $alice->id, 'score' => 6]);

    $this->actingAs(User::factory()->create())->getJson('/api/v1/leaderboards/weekly')->assertOk()
        ->assertJsonPath('data.week_start', now()->startOfWeek()->toDateString())
        ->assertJsonPath('data.music.0.value', 2)
        ->assertJsonPath('data.music.0.user.username', 'Alice')
        ->assertJsonPath('data.kudos', [])
        ->assertJsonPath('data.last_winners.0.board', 'kudos')
        ->assertJsonPath('data.last_winners.0.score', 6);
});

test('recent wins show on the profile widget and old ones do not', function () {
    $alice = User::factory()->create(['username' => 'Alice']);
    WeeklyWinner::query()->create(['week_start' => $this->lastWeek->toDateString(), 'board' => 'music', 'user_id' => $alice->id, 'score' => 4]);
    WeeklyWinner::query()->create(['week_start' => now()->subWeeks(12)->startOfWeek()->toDateString(), 'board' => 'kudos', 'user_id' => $alice->id, 'score' => 9]);

    $wins = app(WeeklyLeaderboardService::class)->recentWins($alice);

    expect($wins->pluck('board')->all())->toBe(['music']);

    $html = view('home.widgets.my-profile', ['user' => $alice])->render();

    expect($html)->toContain('Top DJ')->not->toContain('Most kudos');
});

test('the housekeeping widget lists the latest winners', function () {
    $alice = User::factory()->create(['username' => 'Alice']);
    WeeklyWinner::query()->create(['week_start' => $this->lastWeek->toDateString(), 'board' => 'music', 'user_id' => $alice->id, 'score' => 4, 'badge' => 'ADM']);

    Filament::setCurrentPanel(Filament::getPanel('housekeeping'));

    Livewire::test(WeeklyWinners::class)->assertSee('Alice')->assertSee('Top DJ')->assertSee('ADM');
});

test('a player sees where they stand on the weekly boards', function () {
    $alice = User::factory()->create(['username' => 'Alice']);
    $bob = User::factory()->create(['username' => 'Bob']);
    weeklyPlays('Alice', 2, now()->startOfWeek());
    weeklyPlays('Bob', 4, now()->startOfWeek());

    $standing = app(WeeklyLeaderboardService::class)->standingFor($alice);

    expect($standing)->toBe(['music' => ['rank' => 2, 'value' => 2], 'kudos' => null]);

    $this->actingAs($alice)->get(route('leaderboard.index'))->assertOk()->assertSee('Your standing');
});

test('a player with no weekly score is told how to climb in', function () {
    User::factory()->create(['username' => 'Alice']);
    weeklyPlays('Alice', 2, now()->startOfWeek());

    $this->actingAs(User::factory()->create())->get(route('leaderboard.index'))->assertOk()->assertSee('not on a weekly board yet');
});

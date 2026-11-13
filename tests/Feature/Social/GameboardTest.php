<?php

use App\Emulator\Contracts\RankRepository;
use App\Filament\Resources\Hotel\Social\Pages\ManageGameboards;
use App\Filament\Resources\Hotel\Social\Pages\ManageSocialReports;
use App\Models\Social\Gameboard;
use App\Models\Social\SocialPost;
use App\Models\Social\SocialReport;
use App\Models\User;
use App\Models\WebsiteHousekeepingPermission;
use App\Services\HousekeepingPermissionsService;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;

beforeEach(function () {
    installHotel();
    setSetting('force_staff_2fa', '0');
});

function scoreboard(string $roomName = 'Game Hall'): int
{
    $room = DB::table('rooms')->insertGetId(['name' => $roomName, 'owner_id' => 1, 'owner_name' => 'x']);
    $base = DB::table('items_base')->insertGetId(['public_name' => 'Highscore', 'item_name' => 'highscore_classic*1']);

    return DB::table('items')->insertGetId(['user_id' => 1, 'room_id' => $room, 'item_id' => $base]);
}

it('shows the top scores of a linked scoreboard', function () {
    $item = scoreboard();
    $champ = User::factory()->create(['username' => 'Champ']);
    $second = User::factory()->create(['username' => 'Second']);
    DB::table('items_highscore_data')->insert([
        ['item_id' => $item, 'user_ids' => (string) $second->id, 'score' => 40, 'timestamp' => 1],
        ['item_id' => $item, 'user_ids' => (string) $champ->id, 'score' => 90, 'timestamp' => 1],
    ]);
    Gameboard::query()->create(['name' => 'Speed Run', 'item_id' => $item]);

    $this->actingAs(User::factory()->create())->get(route('gameboards.index'))
        ->assertOk()
        ->assertSeeInOrder(['Speed Run', 'Champ', '90', 'Second', '40']);
});

it('sends gameboard kudos with the board name', function () {
    $board = Gameboard::query()->create(['name' => 'Speed Run', 'item_id' => scoreboard()]);
    $champ = User::factory()->create();

    $this->actingAs(User::factory()->create())->postJson(route('kudos.store'), ['user_id' => $champ->id, 'board' => 'game_' . $board->id])->assertOk();

    expect(DB::table('website_notifications')->where('user_id', $champ->id)->value('message'))->toContain('Speed Run');
});

it('lets housekeeping link scoreboards and handle reports', function () {
    foreach (['can_access_housekeeping', 'manage_social'] as $permission) {
        WebsiteHousekeepingPermission::query()->updateOrCreate(['permission' => $permission], ['min_rank' => 6, 'description' => $permission]);
    }
    app(HousekeepingPermissionsService::class)->refresh();
    Filament::setCurrentPanel(Filament::getPanel('housekeeping'));
    $item = scoreboard('Arcade');
    $post = SocialPost::factory()->create();
    $report = SocialReport::query()->create(['post_id' => $post->id, 'user_id' => User::factory()->create()->id, 'reason' => 'Spam']);

    $this->actingAs(User::factory()->create(['rank' => app(RankRepository::class)->highestRank()]));
    $this->get('/housekeeping/hotel/gameboards')->assertOk();
    $this->get('/housekeeping/hotel/social-reports')->assertOk()->assertSee('Spam');

    Livewire::test(ManageGameboards::class)
        ->callAction('create', ['name' => 'Arcade Kings', 'item_id' => $item, 'order_num' => 0, 'enabled' => true])
        ->assertHasNoActionErrors();
    Livewire::test(ManageSocialReports::class)->callTableAction('remove', $report);

    expect(Gameboard::query()->first()?->room_id)->toBeInt()
        ->and(SocialPost::query()->count())->toBe(0)
        ->and($report->fresh()->resolved_at)->not->toBeNull();
});

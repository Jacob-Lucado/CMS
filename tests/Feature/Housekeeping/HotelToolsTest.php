<?php

use App\Contracts\Rcon;
use App\Exceptions\RconConnectionException;
use App\Filament\Pages\HotelActions;
use App\Filament\Resources\User\DiscordAccounts\Pages\ManageDiscordAccounts;
use App\Filament\Resources\User\Users\Pages\EditUser;
use App\Filament\Resources\User\Users\Widgets\PlayerOverview;
use App\Models\User;
use App\Models\User\WebsiteDiscordAccount;
use App\Models\WebsiteHousekeepingPermission;
use App\Services\FakeRcon;
use App\Services\Hotel\HotelControl;
use App\Services\HousekeepingPermissionsService;
use Filament\Actions\DeleteAction;
use Filament\Facades\Filament;
use Livewire\Livewire;

function allowHousekeeping(string ...$permissions): void
{
    foreach (['can_access_housekeeping', 'edit_user', ...$permissions] as $permission) {
        WebsiteHousekeepingPermission::query()->updateOrCreate(['permission' => $permission], ['min_rank' => 6, 'description' => "Testing {$permission}"]);
    }

    app(HousekeepingPermissionsService::class)->refresh();
}

beforeEach(function () {
    installHotel();
    setSetting('force_staff_2fa', '0');
    Filament::setCurrentPanel(Filament::getPanel('housekeeping'));
});

test('alerting everyone reaches only the players who are online', function () {
    $this->rcon->connected();
    $online = User::factory()->count(2)->create(['online' => '1']);
    User::factory()->create(['online' => '0']);

    $result = app(HotelControl::class)->alertEveryone('Hello hotel');

    expect($result)->toBe(['sent' => 2, 'failed' => 0])
        ->and(collect($this->rcon->calls)->where('method', 'alertUser')->pluck('args.user')->sort()->values()->all())
        ->toBe($online->pluck('id')->sort()->values()->all());
});

test('players the game server cannot reach are counted, not lost', function () {
    User::factory()->count(2)->create(['online' => '1']);
    $this->app->instance(Rcon::class, new class extends FakeRcon
    {
        public function giveBadge(User $user, string $badge): void
        {
            throw new RconConnectionException('Player left');
        }
    });

    expect(app(HotelControl::class)->giveEveryoneBadge('ADM'))->toBe(['sent' => 0, 'failed' => 2]);
});

test('only the reloads this game server knows are offered', function () {
    $hotel = app(HotelControl::class);

    expect(array_keys($hotel->reloads()))->toBe(['catalog', 'filter']);
    expect(fn () => $hotel->reload('navigator'))->toThrow(RconConnectionException::class);
});

test('hotel actions need the manage hotel permission', function (int $rank, int $status) {
    allowHousekeeping();
    WebsiteHousekeepingPermission::query()->updateOrCreate(['permission' => 'manage_hotel'], ['min_rank' => 7, 'description' => 'Testing']);
    app(HousekeepingPermissionsService::class)->refresh();

    $this->actingAs(User::factory()->create(['rank' => $rank]))->get('/housekeeping/hotel-actions')->assertStatus($status);
})->with([
    'below the rank' => [6, 403],
    'at the rank' => [7, 200],
]);

test('staff can alert everyone from the hotel actions page', function () {
    allowHousekeeping('manage_hotel');
    $this->rcon->connected();
    User::factory()->create(['online' => '1']);

    Livewire::actingAs(User::factory()->create(['rank' => 7]))
        ->test(HotelActions::class)
        ->callAction('alertEveryone', data: ['message' => 'Party in the lobby'])
        ->assertHasNoActionErrors()
        ->assertNotified('Alert sent');

    expect(collect($this->rcon->calls)->where('method', 'alertUser')->pluck('args.message')->all())->toBe(['Party in the lobby']);
});

test('staff can find and unlink Discord accounts', function () {
    allowHousekeeping('manage_discord_accounts');
    $links = WebsiteDiscordAccount::factory()->count(2)->create();

    Livewire::actingAs(User::factory()->create(['rank' => 7]))
        ->test(ManageDiscordAccounts::class)
        ->assertCanSeeTableRecords($links)
        ->searchTable($links[0]->discord_id)
        ->assertCanSeeTableRecords([$links[0]])
        ->assertCanNotSeeTableRecords([$links[1]])
        ->callTableAction(DeleteAction::class, $links[0]);

    expect(WebsiteDiscordAccount::query()->whereKey($links[0]->id)->exists())->toBeFalse();
});

test('Discord links stay hidden without the permission', function () {
    allowHousekeeping();
    WebsiteHousekeepingPermission::query()->updateOrCreate(['permission' => 'manage_discord_accounts'], ['min_rank' => 7, 'description' => 'Testing']);
    app(HousekeepingPermissionsService::class)->refresh();

    $this->actingAs(User::factory()->create(['rank' => 6]))->get('/housekeeping/user-management/discord')->assertForbidden();
});

test('staff can alert a single player live', function () {
    allowHousekeeping();
    $this->rcon->connected();
    $player = User::factory()->create(['rank' => 1, 'online' => '1']);

    Livewire::actingAs(User::factory()->create(['rank' => 7]))
        ->test(EditUser::class, ['record' => $player->getRouteKey()])
        ->callAction('alertPlayer', data: ['message' => 'Hi there'])
        ->assertNotified('Alert sent');

    expect(collect($this->rcon->calls)->firstWhere('method', 'alertUser')['args'])
        ->toMatchArray(['user' => $player->id, 'message' => 'Hi there']);
});

test('the player overview shows their Discord and other accounts on the same address', function () {
    $player = User::factory()->create(['ip_register' => '203.0.113.7', 'ip_current' => '203.0.113.7']);
    $alt = User::factory()->create(['ip_register' => '198.51.100.4', 'ip_current' => '203.0.113.7']);
    User::factory()->create(['ip_register' => '192.0.2.1', 'ip_current' => '192.0.2.1']);
    WebsiteDiscordAccount::factory()->create(['user_id' => $player->id, 'discord_username' => 'player.one']);

    Livewire::test(PlayerOverview::class, ['record' => $player])
        ->assertSee('player.one')
        ->assertSee($alt->username)
        ->assertDontSee('192.0.2.1');
});

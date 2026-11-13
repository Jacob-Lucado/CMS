<?php

use App\Emulator\Contracts\RankRepository;
use App\Filament\Resources\Hotel\Radio\Pages\ManageRadioEntries;
use App\Models\Radio\RadioEntry;
use App\Models\User;
use App\Models\WebsiteHousekeepingPermission;
use App\Services\HousekeepingPermissionsService;
use App\Services\Radio\PublicHost;
use App\Services\Radio\RadioAccess;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;

beforeEach(function () {
    installHotel();
    setSetting('force_staff_2fa', '0');
    $this->app->instance(PublicHost::class, new class extends PublicHost
    {
        protected function resolve(string $host): array
        {
            return $host === 'intranet.example.com' ? ['10.0.0.5'] : ['93.184.216.34'];
        }
    });
    Http::fake([
        'soundcloud.com/oembed*' => Http::response([
            'title' => 'Night Drive by Someone',
            'author_name' => 'Someone',
            'thumbnail_url' => 'https://i1.sndcdn.com/artworks-1.jpg',
            'html' => '<iframe src="https://w.soundcloud.com/player/?visual=true&amp;url=https%3A%2F%2Fapi.soundcloud.com%2Ftracks%2F12345&amp;show_artwork=true"></iframe>',
        ]),
        'radio.example.com/live.mp3' => Http::response('', 200, ['Content-Type' => 'audio/mpeg', 'icy-name' => 'Example FM', 'icy-genre' => 'Chill']),
        'radio.example.com/page' => Http::response('<html></html>', 200, ['Content-Type' => 'text/html']),
    ]);
});

/** Each switch starts a fresh session, as a second browser would. */
function listener(User $user): mixed
{
    return test()->flushSession()->actingAs($user);
}

function topRank(): int
{
    return app(RankRepository::class)->highestRank();
}

test('a player suggestion waits for a DJ, who can approve it', function () {
    $player = User::factory()->create(['rank' => 1]);
    $dj = User::factory()->create(['rank' => topRank()]);

    listener($player)->postJson('/radio', ['link' => 'https://radio.example.com/live.mp3'])
        ->assertCreated()
        ->assertJsonPath('entry.status', RadioEntry::PENDING)
        ->assertJsonPath('entry.title', 'Example FM')
        ->assertJsonPath('entry.subtitle', 'Chill');
    $entry = RadioEntry::query()->sole();

    $this->getJson('/radio/feed')->assertJsonPath('entries', [])->assertJsonPath('pending', [])->assertJsonPath('mine.0.id', $entry->id);
    $this->postJson("/radio/{$entry->id}/approve")->assertForbidden();

    listener($dj)->getJson('/radio/feed')->assertJsonPath('dj', true)->assertJsonPath('pending.0.id', $entry->id);
    $this->postJson("/radio/{$entry->id}/approve")->assertOk();

    expect($entry->fresh()->status)->toBe(RadioEntry::APPROVED)
        ->and($entry->fresh()->reviewed_by)->toBe($dj->id);
    listener($player)->getJson('/radio/feed')->assertJsonPath('entries.0.id', $entry->id);
});

test('a DJ adds a SoundCloud track straight to the radio', function () {
    listener(User::factory()->create(['rank' => topRank()]))
        ->postJson('/radio', ['link' => 'https://soundcloud.com/someone/night-drive'])
        ->assertCreated()
        ->assertJsonPath('entry.status', RadioEntry::APPROVED)
        ->assertJsonPath('entry.kind', RadioEntry::TRACK)
        ->assertJsonPath('entry.title', 'Night Drive')
        ->assertJsonPath('entry.stream', 'https://api.soundcloud.com/tracks/12345');

    $this->postJson('/radio', ['link' => 'https://soundcloud.com/someone/night-drive'])->assertJsonValidationErrors('link');
});

test('the roles picked in housekeeping become DJs', function () {
    $ranks = app(RankRepository::class);
    $role = collect(array_keys($ranks->roleOptions()))->first(fn ($key) => (int) $key < topRank() && (int) $key > 1);
    $member = User::factory()->create(['rank' => $role]);
    $access = app(RadioAccess::class);

    expect($access->isDj($member))->toBeFalse();

    $access->configure([$role], true);

    expect($access->isDj($member))->toBeTrue()
        ->and($access->isDj(User::factory()->create(['rank' => 1])))->toBeFalse()
        ->and($access->isDj(User::factory()->create(['rank' => topRank()])))->toBeTrue();
});

test('closing suggestions stops players but not DJs', function () {
    app(RadioAccess::class)->configure([], false);

    listener(User::factory()->create(['rank' => 1]))
        ->postJson('/radio', ['link' => 'https://radio.example.com/live.mp3'])->assertForbidden();
    listener(User::factory()->create(['rank' => topRank()]))
        ->postJson('/radio', ['link' => 'https://radio.example.com/live.mp3'])->assertCreated();
});

test('links that are not public audio streams are turned away', function (string $link) {
    listener(User::factory()->create(['rank' => 1]))
        ->postJson('/radio', ['link' => $link])
        ->assertJsonValidationErrors('link');

    expect(RadioEntry::query()->count())->toBe(0);
})->with([
    'plain http' => 'http://radio.example.com/live.mp3',
    'a web page' => 'https://radio.example.com/page',
    'a private address' => 'https://intranet.example.com/live.mp3',
    'a raw private IP' => 'https://127.0.0.1/live.mp3',
    'an odd port' => 'https://radio.example.com:22/live.mp3',
]);

test('a player can only have a few suggestions waiting at once', function () {
    $player = User::factory()->create(['rank' => 1]);
    RadioEntry::factory()->pending()->count(RadioAccess::MAX_PENDING_PER_PLAYER)->create(['user_id' => $player->id]);

    listener($player)->postJson('/radio', ['link' => 'https://radio.example.com/live.mp3'])->assertJsonValidationErrors('link');
});

test('a DJ picks what is on air and removing it takes it off air', function () {
    $entry = RadioEntry::factory()->create();
    $pending = RadioEntry::factory()->pending()->create();

    listener(User::factory()->create(['rank' => 1]))->postJson('/radio/on-air', ['id' => $entry->id])->assertForbidden();

    listener(User::factory()->create(['rank' => topRank()]));
    $this->postJson('/radio/on-air', ['id' => $pending->id])->assertNotFound();
    $this->postJson('/radio/on-air', ['id' => $entry->id])->assertOk();
    $this->getJson('/radio/feed')->assertJsonPath('on_air.id', $entry->id);

    $this->deleteJson("/radio/{$entry->id}")->assertOk();
    $this->getJson('/radio/feed')->assertJsonPath('on_air', null);
});

test('players can withdraw their own suggestions and nothing else', function () {
    $player = User::factory()->create(['rank' => 1]);
    $mine = RadioEntry::factory()->pending()->create(['user_id' => $player->id]);
    $approved = RadioEntry::factory()->create(['user_id' => $player->id]);
    $theirs = RadioEntry::factory()->pending()->create();

    listener($player);
    $this->deleteJson("/radio/{$approved->id}")->assertForbidden();
    $this->deleteJson("/radio/{$theirs->id}")->assertForbidden();
    $this->deleteJson("/radio/{$mine->id}")->assertOk();

    expect(RadioEntry::query()->pluck('id')->sort()->values()->all())->toBe([$approved->id, $theirs->id]);
});

test('the radio page opens for signed in players', function () {
    $this->get('/radio')->assertRedirect();
    listener(User::factory()->create())->get('/radio')->assertOk()->assertSee('hotelRadio');
});

test('housekeeping sets the DJ roles and reviews suggestions', function () {
    foreach (['can_access_housekeeping', 'manage_radio'] as $permission) {
        WebsiteHousekeepingPermission::query()->updateOrCreate(['permission' => $permission], ['min_rank' => 6, 'description' => $permission]);
    }
    app(HousekeepingPermissionsService::class)->refresh();
    Filament::setCurrentPanel(Filament::getPanel('housekeeping'));
    $entry = RadioEntry::factory()->pending()->create();

    listener(User::factory()->create(['rank' => 5]))->get('/housekeeping/hotel/radio')->assertForbidden();

    $staff = User::factory()->create(['rank' => topRank()]);
    listener($staff)->get('/housekeeping/hotel/radio')->assertOk();
    Livewire::test(ManageRadioEntries::class)
        ->callAction('settings', ['roles' => [5], 'submissions' => false])
        ->assertHasNoActionErrors()
        ->callTableAction('approve', $entry);

    expect(app(RadioAccess::class)->djRoles())->toBe([5])
        ->and(app(RadioAccess::class)->submissionsOpen())->toBeFalse()
        ->and($entry->fresh()->status)->toBe(RadioEntry::APPROVED);
});

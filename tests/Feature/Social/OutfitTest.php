<?php

use App\Models\Social\Outfit;
use App\Models\Social\SocialPost;
use App\Models\Social\WebsiteNotification;
use App\Models\User;
use Illuminate\Support\Carbon;

beforeEach(function () {
    installHotel();
    setSetting('force_staff_2fa', '0');
});

it('enters the outfit a player is wearing, once per week', function () {
    $player = User::factory()->create(['look' => 'hr-100-61.hd-180-1']);

    $this->actingAs($player)->postJson(route('outfits.store'))->assertCreated()->assertJsonPath('entry.look', 'hr-100-61.hd-180-1');
    $player->forceFill(['look' => 'hr-115-42.hd-190-1'])->save();
    $this->actingAs($player)->postJson(route('outfits.store'))->assertOk();

    expect(Outfit::query()->count())->toBe(1)
        ->and(Outfit::query()->value('look'))->toBe('hr-115-42.hd-190-1');
});

it('toggles votes, refuses your own entry and tells the owner', function () {
    $owner = User::factory()->create();
    $fan = User::factory()->create();
    $this->actingAs($owner)->postJson(route('outfits.store'));
    $entry = Outfit::query()->firstOrFail();

    $this->flushSession()->actingAs($owner)->postJson(route('outfits.vote', $entry))->assertUnprocessable();
    $this->flushSession()->actingAs($fan)->postJson(route('outfits.vote', $entry))->assertJson(['voted' => true, 'votes' => 1]);
    $this->flushSession()->actingAs($fan)->postJson(route('outfits.vote', $entry))->assertJson(['voted' => false, 'votes' => 0]);

    expect(WebsiteNotification::query()->where('user_id', $owner->id)->where('type', 'outfit_like')->count())->toBe(1);
});

it('picks last week\'s winner by votes and posts it to the feed', function () {
    $lastWeek = Carbon::now()->startOfWeek()->subWeek()->toDateString();
    $runnerUp = Outfit::query()->create(['user_id' => User::factory()->create()->id, 'week' => $lastWeek, 'look' => 'a', 'votes' => 2]);
    $winner = Outfit::query()->create(['user_id' => User::factory()->create()->id, 'week' => $lastWeek, 'look' => 'b', 'votes' => 5]);

    $this->actingAs(User::factory()->create())->get(route('outfits.index'))->assertOk()->assertSee('outfitWeek');
    $this->get(route('outfits.index'))->assertOk();

    expect($winner->fresh()->winner)->toBeTrue()
        ->and($runnerUp->fresh()->winner)->toBeFalse()
        ->and(SocialPost::query()->where('user_id', $winner->user_id)->count())->toBe(1);
});

it('closes voting on past weeks', function () {
    $old = Outfit::query()->create(['user_id' => User::factory()->create()->id, 'week' => Carbon::now()->startOfWeek()->subWeek()->toDateString(), 'look' => 'a']);

    $this->actingAs(User::factory()->create())->postJson(route('outfits.vote', $old))->assertUnprocessable();
});

it('shares a posed avatar to the feed without letting the figure be swapped', function () {
    $player = User::factory()->create(['look' => 'hr-100-61']);

    $this->actingAs($player)->postJson(route('social.posts.store'), ['type' => 'outfit', 'avatar' => 'direction=4&action=wav&figure=evil'])
        ->assertCreated()
        ->assertJsonPath('post.data.avatar', 'direction=4&action=wav')
        ->assertJsonPath('post.data.look', 'hr-100-61');
});

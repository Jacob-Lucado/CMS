<?php

use App\Models\Social\SocialPost;
use App\Models\User;
use Illuminate\Support\Facades\DB;

beforeEach(function () {
    installHotel();
    setSetting('force_staff_2fa', '0');
    setSetting('furniture_icons_path', '/icons');
});

function marketOffer(User $seller, string $name, int $price, int $state = 1): int
{
    $base = DB::table('items_base')->insertGetId(['public_name' => $name, 'item_name' => strtolower(str_replace(' ', '_', $name))]);
    $item = DB::table('items')->insertGetId(['user_id' => $seller->id, 'item_id' => $base]);

    return DB::table('marketplace_items')->insertGetId(['item_id' => $item, 'user_id' => $seller->id, 'price' => $price, 'timestamp' => now()->timestamp, 'state' => $state, 'sold_timestamp' => $state === 2 ? now()->timestamp : 0]);
}

it('shows listings cheapest first, recent sales and top movers', function () {
    $seller = User::factory()->create(['username' => 'Trader']);
    marketOffer($seller, 'Throne', 900);
    marketOffer($seller, 'Dragon Lamp', 300);
    marketOffer($seller, 'Club Sofa', 50, state: 2);

    $this->actingAs(User::factory()->create())->get(route('marketplace.index'))
        ->assertOk()
        ->assertSeeInOrder(['Dragon Lamp', 'Throne'])
        ->assertSee('Club Sofa')
        ->assertSee('dragon_lamp_icon.png');
});

it('searches listings by name', function () {
    $seller = User::factory()->create();
    marketOffer($seller, 'Throne', 900);
    marketOffer($seller, 'Dragon Lamp', 300);

    $this->actingAs(User::factory()->create())->getJson(route('marketplace.search', ['q' => 'thro']))
        ->assertOk()
        ->assertJsonCount(1, 'listings')
        ->assertJsonPath('listings.0.seller', $seller->username);
});

it('shares a listing to the feed but not one that sold', function () {
    $seller = User::factory()->create();
    $open = marketOffer($seller, 'Throne', 900);
    $sold = marketOffer($seller, 'Club Sofa', 50, state: 2);
    $sharer = User::factory()->create();

    $this->actingAs($sharer)->postJson(route('social.posts.store'), ['type' => 'listing', 'offer_id' => $open])
        ->assertCreated()
        ->assertJsonPath('post.data.name', 'Throne')
        ->assertJsonPath('post.data.price', 900);
    $this->actingAs($sharer)->postJson(route('social.posts.store'), ['type' => 'listing', 'offer_id' => $sold])->assertUnprocessable();

    expect(SocialPost::query()->count())->toBe(1);
});

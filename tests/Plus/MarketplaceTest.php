<?php

use App\Emulator\Contracts\MarketplaceRepository;
use App\Models\User;
use Illuminate\Support\Facades\DB;

beforeEach(function () {
    DB::table('roles')->insert(['id' => 1, 'slug' => 'default', 'name' => 'User', 'description' => '', 'weight' => 0, 'security_level' => 1, 'badge_code' => '', 'is_staff' => false, 'is_hidden' => false, 'created_at' => now(), 'updated_at' => now()]);
});

test('plus marketplace offers and sales read from the catalog tables', function () {
    $furni = DB::table('furniture')->insertGetId(['item_name' => 'throne', 'public_name' => 'Throne', 'type' => 's', 'sprite_id' => 77]);
    $seller = User::factory()->create();
    $row = ['item_id' => 1, 'user_id' => $seller->id, 'public_name' => 'throne', 'sprite_id' => 77, 'extra_data' => '', 'furni_id' => $furni, 'listed_at' => now()];
    DB::table('catalog_marketplace_offers')->insert([[...$row, 'asking_price' => 100, 'total_price' => 101, 'state' => '1'], [...$row, 'asking_price' => 80, 'total_price' => 81, 'state' => '2']]);
    DB::table('catalog_marketplace_data')->insert(['sprite' => 77, 'sold' => 3, 'avgprice' => 90]);

    $market = app(MarketplaceRepository::class);

    expect($market->listings(10)->map(fn ($offer) => [$offer->name, $offer->price])->all())->toBe([['Throne', 101]])
        ->and($market->recentSales(10)->first()?->sold)->toBeTrue()
        ->and($market->topMovers(5)->first())->toMatchArray(['name' => 'Throne', 'itemName' => 'throne', 'sold' => 3, 'averagePrice' => 90])
        ->and($market->listings(10, 'nope'))->toBeEmpty();
});

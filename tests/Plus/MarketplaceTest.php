<?php

use App\Emulator\Contracts\MarketplaceRepository;
use App\Filament\Resources\Hotel\Social\Pages\ManagePublicRooms;
use App\Models\User;
use App\Models\WebsiteHousekeepingPermission;
use App\Services\HousekeepingPermissionsService;
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

test('housekeeping lists a room in the public navigator tab', function () {
    foreach (['can_access_housekeeping', 'manage_social'] as $permission) {
        WebsiteHousekeepingPermission::query()->updateOrCreate(['permission' => $permission], ['min_rank' => 1, 'description' => $permission]);
    }
    app(HousekeepingPermissionsService::class)->refresh();
    Filament\Facades\Filament::setCurrentPanel(Filament\Facades\Filament::getPanel('housekeeping'));
    DB::table('roles')->insert(['id' => 9, 'slug' => 'owner', 'name' => 'Owner', 'description' => '', 'weight' => 90, 'security_level' => 7, 'badge_code' => '', 'is_staff' => true, 'is_hidden' => false, 'created_at' => now(), 'updated_at' => now()]);
    $owner = User::factory()->create(['rank' => 7]);
    DB::statement('SET FOREIGN_KEY_CHECKS=0');
    $room = DB::table('rooms')->insertGetId(['caption' => 'Welcome Lounge', 'owner' => $owner->id, 'description' => 'Say hi', 'model_name' => 'model_a']);
    DB::statement('SET FOREIGN_KEY_CHECKS=1');

    $this->actingAs($owner)->get('/housekeeping/hotel/public-rooms')->assertOk();
    Livewire\Livewire::test(ManagePublicRooms::class)
        ->callAction('create', ['room_id' => $room, 'caption' => 'Welcome Lounge', 'description' => 'Say hi', 'order_num' => 1, 'enabled' => true])
        ->assertHasNoActionErrors();

    expect(DB::table('navigator_publics')->where('room_id', $room)->value('caption'))->toBe('Welcome Lounge');
});

<?php

use App\Models\Shop\WebsiteShopPurchase;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Ada half of the shop package suite; the shared cases are in tests/Feature.
 * Credits land in player_data rather than on the users row, and Ada has no
 * RCON bridge, so an online purchase must refuse rather than desync.
 */
test('an offline ada package purchase uses the ada database driver', function () {
    installHotel();

    $user = User::factory()->create(['website_balance' => 2000]);
    $package = makePackage();
    $startingCredits = (int) DB::table('player_data')->where('player_id', $user->id)->value('credit_balance');

    $this->actingAs($user)
        ->post(route('shop.buy-package', $package), [])
        ->assertSessionHas('success');

    expect((int) DB::table('player_data')->where('player_id', $user->id)->value('credit_balance'))
        ->toBe($startingCredits + 200)
        ->and((int) $user->refresh()->website_balance)->toBe(1500);
});

test('an online ada package purchase fails safely without rcon', function () {
    installHotel();

    $user = User::factory()->create(['website_balance' => 2000]);
    DB::table('player_data')->where('player_id', $user->id)->update(['is_online' => true]);
    $user = $user->refresh();
    $package = makePackage();
    $startingCredits = (int) DB::table('player_data')->where('player_id', $user->id)->value('credit_balance');

    $this->actingAs($user)
        ->post(route('shop.buy-package', $package), [])
        ->assertSessionHasErrors('message');

    expect((int) $user->refresh()->website_balance)->toBe(2000)
        ->and((int) DB::table('player_data')->where('player_id', $user->id)->value('credit_balance'))
        ->toBe($startingCredits)
        ->and(WebsiteShopPurchase::where('user_id', $user->id)->count())->toBe(0);
});

test('an online ada player cannot claim a referral reward', function () {
    installHotel();
    setSetting('referrals_needed', '3');
    setSetting('referral_reward_amount', '10');

    // Ada has no RCON bridge, so an online player is always in the position
    // where a database grant would be overwritten on disconnect.
    $user = User::factory()->create();
    $user->referrals()->create(['referrals_total' => 4]);
    DB::table('player_data')->where('player_id', $user->id)->update(['is_online' => true]);
    $before = (int) DB::table('player_data')->where('player_id', $user->id)->value('seasonal_balance');

    $this->actingAs($user->refresh())
        ->post(route('claim.referral-reward'))
        ->assertSessionHasErrors('message');

    // The referrals survive, so the reward stays claimable once they log out.
    expect($user->referrals->fresh()->referrals_total)->toBe(4)
        ->and((int) DB::table('player_data')->where('player_id', $user->id)->value('seasonal_balance'))->toBe($before);
});

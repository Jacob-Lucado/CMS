<?php

use App\Models\User;

beforeEach(function () {
    installHotel();
    setSetting('enable_caching', '0');
    config(['services.discord.client_id' => '123456789012345678', 'services.discord.client_secret' => 'secret']);
});

test('every theme offers Discord on its sign-in', function (string $theme) {
    setSetting('theme', $theme);

    $this->get(route('welcome'))->assertOk()->assertSee(route('discord.redirect'), false);
})->with(['atom', 'dusk', 'lucadoos', 'lucado', 'mercury', 'revpro']);

test('every theme offers Discord when registering', function (string $theme) {
    setSetting('theme', $theme);

    $this->get(route('register'))->assertOk()->assertSee(route('discord.redirect'), false);
})->with(['atom', 'dusk', 'lucadoos', 'lucado', 'mercury', 'revpro']);

test('every theme lets a player link and unlink Discord in account settings', function (string $theme) {
    setSetting('theme', $theme);

    $this->actingAs(User::factory()->create())->get(route('settings.account.show'))
        ->assertOk()
        ->assertSee(route('discord.redirect'), false);
})->with(['atom', 'dusk', 'lucadoos', 'lucado', 'mercury', 'revpro']);

<?php

use App\Models\User;

test('the air client page is hidden until the air client is turned on', function () {
    installHotel();

    config(['habbo.client.air_enabled' => false]);

    $this->actingAs(User::factory()->create())->get(route('air-client'))->assertNotFound();
});

test('the air client page gives the launcher link with a fresh ticket', function () {
    installHotel();

    config(['habbo.client.air_enabled' => true, 'app.url' => 'https://retro.example.com']);

    $user = User::factory()->create(['auth_ticket' => '']);

    $this->actingAs($user)
        ->get(route('air-client'))
        ->assertOk()
        ->assertSee('Open AIR client')
        ->assertSee('habbo://hab?server=retro.example.com&amp;token=', false);

    expect($user->fresh()->auth_ticket)->not->toBe('');
});

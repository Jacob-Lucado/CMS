<?php

use App\Models\User;
use App\Services\SettingsService;

beforeEach(function () {
    installHotel();
    setSetting('theme', 'mercury');
    setSetting('enable_caching', '0');
});

test('a visitor gets the welcome area with the login card', function () {
    $this->get(route('welcome'))
        ->assertOk()
        ->assertSee('mercury-hero', false)
        ->assertSee('mercury-login', false)
        ->assertSee(route('login'), false)
        ->assertSee(route('register'), false);
});

test('a signed in player gets the banner and the news', function () {
    $this->actingAs(User::factory()->create())->get(route('article.index'))
        ->assertOk()
        ->assertSee('mercury-banner', false)
        ->assertDontSee('mercury-login', false);
});

test('pages the theme does not change still render', function () {
    $this->get(route('article.index'))->assertOk();
});

test('visitors can switch to the theme', function () {
    setSetting('theme', 'atom');

    $this->get(route('theme.switch', 'mercury'))->assertRedirect();

    $this->withUnencryptedCookie(SettingsService::THEME_COOKIE, 'mercury')
        ->get(route('welcome'))
        ->assertOk()
        ->assertSee('mercury-hero', false);
});

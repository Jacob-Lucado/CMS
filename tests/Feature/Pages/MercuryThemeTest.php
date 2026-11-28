<?php

use App\Models\User;
use App\Services\SettingsService;
use Database\Seeders\WebsiteSettingsSeeder;

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

test('every public page renders in the theme', function (string $route) {
    $this->get(route($route))->assertSuccessful();
})->with(['register', 'forgot.password.get', 'article.index']);

test('every hotel page renders in the theme for a player', function (string $route) {
    $this->actingAs(User::factory()->create())->get(route($route))->assertSuccessful();
})->with([
    'me.show', 'article.index', 'shop.index', 'music.index', 'hall-of-fame.index', 'suggestions.index', 'riddle.show',
    'polls.index', 'radio.index', 'changelog.index', 'leaderboard.index', 'events.index', 'furni.index', 'values.index',
    'catalog.index', 'daily-reward.show', 'social.index', 'notifications.index', 'settings.account.show',
    'help-center.index', 'staff.index', 'teams.index', 'photos.index', 'gameboards.index',
]);

test('the menu offers the other themes but not the current one', function () {
    $this->get(route('welcome'))
        ->assertSee(route('theme.switch', 'lucadoos'), false)
        ->assertSee(route('theme.switch', 'atom'), false)
        ->assertDontSee(route('theme.switch', 'mercury'), false);
});

test('the header uses the Lucado logo by default', function () {
    $this->seed(WebsiteSettingsSeeder::class);

    expect(setting('cms_logo'))->toBe('/assets/images/lucado-logo.png');
});

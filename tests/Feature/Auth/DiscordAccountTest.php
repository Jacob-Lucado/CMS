<?php

use App\Models\User;
use App\Models\User\WebsiteDiscordAccount;
use Illuminate\Support\Facades\Http;
use Illuminate\Testing\TestResponse;

beforeEach(function () {
    installHotel();
    config(['services.discord.client_id' => '123456789012345678', 'services.discord.client_secret' => 'secret']);
});

function fakeDiscord(array $profile = []): void
{
    Http::fake([
        'discord.com/api/oauth2/token' => Http::response(['access_token' => 'token', 'token_type' => 'Bearer']),
        'discord.com/api/users/@me' => Http::response([
            'id' => '111122223333444455',
            'username' => 'jacob',
            'global_name' => 'Jacob',
            'avatar' => 'abc123',
            'email' => 'jacob@example.com',
            'verified' => true,
            ...$profile,
        ]),
    ]);
}

function returnFromDiscord(): TestResponse
{
    test()->get(route('discord.redirect'));
    $state = session('discord.state');

    return test()->get(route('discord.callback', ['code' => 'code', 'state' => $state]));
}

test('the Discord button sends the player to Discord', function () {
    $response = $this->get(route('discord.redirect'));

    $response->assertRedirect();
    expect($response->headers->get('Location'))
        ->toStartWith('https://discord.com/oauth2/authorize?')
        ->toContain('state=' . session('discord.state'))
        ->toContain(urlencode(route('discord.callback')));
});

test('Discord sign-in stays off until it is set up', function () {
    config(['services.discord.client_id' => null]);

    $this->from(route('welcome'))->get(route('discord.redirect'))
        ->assertRedirect(route('welcome'))
        ->assertSessionHas('message');
});

test('a callback without the matching state is refused', function () {
    fakeDiscord();
    $this->get(route('discord.redirect'));

    $this->get(route('discord.callback', ['code' => 'code', 'state' => 'forged']))
        ->assertRedirect(route('welcome'))
        ->assertSessionHas('message');

    $this->assertGuest();
    Http::assertNothingSent();
});

test('a player with a linked Discord is signed in', function () {
    fakeDiscord();
    $account = WebsiteDiscordAccount::factory()->create(['discord_id' => '111122223333444455']);

    returnFromDiscord()->assertRedirect(route('me.show'));

    $this->assertAuthenticatedAs($account->user);
    expect($account->fresh()->discord_username)->toBe('Jacob');
});

test('a player with two-factor sign-in still has to enter the code', function () {
    fakeDiscord();
    $user = User::factory()->create();
    $user->forceFill(['two_factor_secret' => encrypt('secret'), 'two_factor_confirmed_at' => now()])->save();
    WebsiteDiscordAccount::factory()->create(['user_id' => $user->id, 'discord_id' => '111122223333444455']);

    returnFromDiscord()->assertRedirect(route('two-factor.login'));

    $this->assertGuest();
    expect(session('login.id'))->toBe($user->id);
});

test('a new Discord goes on to register and is linked to the new account', function () {
    fakeDiscord();

    returnFromDiscord()->assertRedirect(route('register'));
    $this->get(route('register'))->assertOk()->assertSee('Jacob')->assertSee('jacob@example.com');

    $this->post('/register', [
        'username' => 'Jacob',
        'mail' => 'jacob@example.com',
        'password' => 'Sup3rSecret!',
        'password_confirmation' => 'Sup3rSecret!',
        'terms' => 'on',
    ])->assertSessionHasNoErrors();

    $user = User::where('username', 'Jacob')->firstOrFail();
    expect($user->discordAccount?->discord_id)->toBe('111122223333444455');
});

test('a signed in player can link and unlink Discord', function () {
    fakeDiscord();
    $user = User::factory()->create();
    $this->actingAs($user);

    returnFromDiscord()->assertRedirect(route('me.show'))->assertSessionHas('success');
    expect($user->discordAccount()->value('discord_id'))->toBe('111122223333444455');

    $this->delete(route('settings.discord.destroy'))->assertSessionHas('success');
    expect($user->discordAccount()->exists())->toBeFalse();
});

test('a Discord linked to someone else cannot be linked again', function () {
    fakeDiscord();
    WebsiteDiscordAccount::factory()->create(['discord_id' => '111122223333444455']);
    $user = User::factory()->create();
    $this->actingAs($user);

    returnFromDiscord()->assertRedirect(route('me.show'))->assertSessionHas('message');

    expect($user->discordAccount()->exists())->toBeFalse();
});

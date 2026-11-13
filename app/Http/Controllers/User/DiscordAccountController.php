<?php

namespace App\Http\Controllers\User;

use App\Exceptions\DiscordLoginException;
use App\Http\Controllers\Controller;
use App\Models\User\WebsiteDiscordAccount;
use App\Services\Auth\DiscordLogin;
use App\Support\AuthenticatedUser;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Laravel\Fortify\Fortify;

/**
 * Sign in, register and link with Discord. A guest whose Discord is linked is signed in; a guest whose
 * Discord is new goes on to register and the account is linked once it exists; a signed-in player links.
 */
class DiscordAccountController extends Controller
{
    public function __construct(private readonly DiscordLogin $discord) {}

    public function redirect(Request $request): RedirectResponse
    {
        if (! $this->discord->isEnabled()) {
            return back()->with('message', __('Discord sign-in is not set up yet.'));
        }

        $state = Str::random(40);
        $request->session()->put('discord.state', $state);
        $request->session()->put('discord.intent', Auth::check() ? 'link' : 'login');

        return redirect()->away($this->discord->authorizeUrl($state));
    }

    public function callback(Request $request): RedirectResponse
    {
        $state = $request->session()->pull('discord.state');
        $intent = $request->session()->pull('discord.intent', 'login');
        $code = $request->query('code');

        if (! is_string($state) || ! is_string($request->query('state')) || ! hash_equals($state, $request->query('state'))) {
            return $this->leave(__('That Discord sign-in expired. Please try again.'));
        }

        if (! is_string($code) || $code === '') {
            return $this->leave(__('Discord sign-in was cancelled.'));
        }

        try {
            $profile = $this->discord->profile($code);
        } catch (DiscordLoginException) {
            return $this->leave(__('Discord did not answer. Please try again in a moment.'));
        }

        $account = WebsiteDiscordAccount::query()->where('discord_id', $profile['id'])->first();
        $account?->update(['discord_username' => $profile['username'], 'discord_avatar' => $profile['avatar']]);

        if ($intent === 'link' && Auth::check()) {
            return $this->link($profile, $account);
        }

        if (Auth::check()) {
            return redirect()->route('me.show');
        }

        if ($account === null) {
            $request->session()->put('discord.pending', $profile);

            return redirect()->route('register')
                ->with('success', __('Pick a name for your hotel account and we will link it to your Discord.'));
        }

        $user = $account->user;

        if ($user === null) {
            return $this->leave(__('That Discord account is not linked to a player any more.'));
        }

        // Accounts with two-factor sign-in still have to pass the code check, exactly as with a password.
        if (Fortify::confirmsTwoFactorAuthentication() ? $user->two_factor_confirmed_at !== null : filled($user->two_factor_secret)) {
            $request->session()->put(['login.id' => $user->getKey(), 'login.remember' => true]);

            return redirect()->route('two-factor.login');
        }

        Auth::login($user, true);
        $request->session()->regenerate();

        return redirect()->intended(route('me.show'));
    }

    public function destroy(): RedirectResponse
    {
        AuthenticatedUser::current()->discordAccount()->delete();

        return back()->with('success', __('Your Discord account is no longer linked.'));
    }

    /**
     * @param  array{id: string, username: string, avatar: string|null, email: string|null}  $profile
     */
    private function link(array $profile, ?WebsiteDiscordAccount $account): RedirectResponse
    {
        $user = AuthenticatedUser::current();

        if ($account !== null && $account->user_id !== $user->getKey()) {
            return redirect()->route('me.show')->with('message', __('That Discord account is already linked to another player.'));
        }

        WebsiteDiscordAccount::query()->updateOrCreate(
            ['user_id' => $user->getKey()],
            ['discord_id' => $profile['id'], 'discord_username' => $profile['username'], 'discord_avatar' => $profile['avatar']],
        );

        return redirect()->route('me.show')->with('success', __('Your Discord account is linked.'));
    }

    private function leave(string $message): RedirectResponse
    {
        return redirect()->route(Auth::check() ? 'me.show' : 'welcome')->with('message', $message);
    }
}

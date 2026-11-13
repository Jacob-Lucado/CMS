<?php

namespace App\Services\Auth;

use App\Exceptions\DiscordLoginException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

/**
 * Discord's OAuth2 sign-in: send the player to Discord, then trade the code it hands back for their Discord profile.
 */
class DiscordLogin
{
    private const AUTHORIZE_URL = 'https://discord.com/oauth2/authorize';

    private const TOKEN_URL = 'https://discord.com/api/oauth2/token';

    private const PROFILE_URL = 'https://discord.com/api/users/@me';

    public function isEnabled(): bool
    {
        return filled(config('services.discord.client_id')) && filled(config('services.discord.client_secret'));
    }

    public function authorizeUrl(string $state): string
    {
        return self::AUTHORIZE_URL . '?' . http_build_query([
            'client_id' => config('services.discord.client_id'),
            'redirect_uri' => $this->redirectUri(),
            'response_type' => 'code',
            'scope' => 'identify email',
            'state' => $state,
            'prompt' => 'none',
        ]);
    }

    /**
     * @throws DiscordLoginException
     *
     * @return array{id: string, username: string, avatar: string|null, email: string|null}
     */
    public function profile(string $code): array
    {
        try {
            $token = Http::asForm()->acceptJson()->timeout(10)->post(self::TOKEN_URL, [
                'client_id' => config('services.discord.client_id'),
                'client_secret' => config('services.discord.client_secret'),
                'grant_type' => 'authorization_code',
                'code' => $code,
                'redirect_uri' => $this->redirectUri(),
            ]);

            if (! $token->successful() || ! is_string($token->json('access_token'))) {
                throw new DiscordLoginException('Discord did not accept the sign-in code.');
            }

            $profile = Http::withToken($token->json('access_token'))->acceptJson()->timeout(10)->get(self::PROFILE_URL);
        } catch (ConnectionException) {
            throw new DiscordLoginException('Discord could not be reached.');
        }

        $id = $profile->json('id');

        if (! $profile->successful() || ! is_string($id) || preg_match('/^\d{5,32}$/', $id) !== 1) {
            throw new DiscordLoginException('Discord did not send a profile.');
        }

        $name = $profile->json('global_name') ?: $profile->json('username');
        $email = $profile->json('verified') === true ? $profile->json('email') : null;

        return [
            'id' => $id,
            'username' => mb_substr(is_string($name) ? $name : $id, 0, 64),
            'avatar' => is_string($profile->json('avatar')) ? mb_substr($profile->json('avatar'), 0, 64) : null,
            'email' => is_string($email) ? $email : null,
        ];
    }

    public function redirectUri(): string
    {
        return route('discord.callback');
    }
}

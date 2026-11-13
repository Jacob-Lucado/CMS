<?php

namespace App\Listeners;

use App\Models\User\WebsiteDiscordAccount;
use Illuminate\Auth\Events\Registered;

/**
 * A player who came from "Continue with Discord" gets that Discord linked as soon as the account exists.
 */
class LinkPendingDiscordAccount
{
    public function handle(Registered $event): void
    {
        if (! app()->bound('session') || ! session()->isStarted()) {
            return;
        }

        $profile = session()->pull('discord.pending');

        if (! is_array($profile) || ! is_string($profile['id'] ?? null)) {
            return;
        }

        if (WebsiteDiscordAccount::query()->where('discord_id', $profile['id'])->exists()) {
            return;
        }

        WebsiteDiscordAccount::query()->create([
            'user_id' => $event->user->getAuthIdentifier(),
            'discord_id' => $profile['id'],
            'discord_username' => (string) ($profile['username'] ?? $profile['id']),
            'discord_avatar' => is_string($profile['avatar'] ?? null) ? $profile['avatar'] : null,
        ]);
    }
}

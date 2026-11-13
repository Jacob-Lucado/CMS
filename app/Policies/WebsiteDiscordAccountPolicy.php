<?php

namespace App\Policies;

use App\Models\User;

/**
 * Staff can look up and unlink Discord accounts, but only players link them, through Discord itself.
 */
class WebsiteDiscordAccountPolicy extends HousekeepingPolicy
{
    protected function permission(): string
    {
        return 'manage_discord_accounts';
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, mixed $record = null): bool
    {
        return false;
    }
}

<?php

namespace App\Policies;

use App\Models\User;

/**
 * Radio entries come in through the Radio app, so Housekeeping reviews and removes them but never writes one.
 */
class RadioEntryPolicy extends HousekeepingPolicy
{
    protected function permission(): string
    {
        return 'manage_radio';
    }

    public function create(User $user): bool
    {
        return false;
    }
}

<?php

namespace App\Policies;

use App\Models\User;

/**
 * Reports come in from the social feed, so Housekeeping reviews them but never writes one.
 */
class SocialReportPolicy extends HousekeepingPolicy
{
    protected function permission(): string
    {
        return 'manage_social';
    }

    public function create(User $user): bool
    {
        return false;
    }
}

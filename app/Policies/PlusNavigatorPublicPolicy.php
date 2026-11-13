<?php

namespace App\Policies;

class PlusNavigatorPublicPolicy extends HousekeepingPolicy
{
    protected function permission(): string
    {
        return 'manage_social';
    }
}

<?php

namespace App\Policies;

class AdaPlayerBadgePolicy extends HousekeepingPolicy
{
    protected function permission(): string
    {
        return 'edit_user';
    }
}

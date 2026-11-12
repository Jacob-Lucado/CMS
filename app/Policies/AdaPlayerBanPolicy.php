<?php

namespace App\Policies;

class AdaPlayerBanPolicy extends HousekeepingPolicy
{
    protected function permission(): string
    {
        return 'manage_bans';
    }
}

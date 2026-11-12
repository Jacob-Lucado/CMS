<?php

namespace App\Policies;

class AdaIpBanPolicy extends HousekeepingPolicy
{
    protected function permission(): string
    {
        return 'manage_bans';
    }
}

<?php

namespace App\Policies;

class RoomStreamBanPolicy extends HousekeepingPolicy
{
    protected function permission(): string
    {
        return 'manage_wordfilter';
    }
}

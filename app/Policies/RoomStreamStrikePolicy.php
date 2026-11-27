<?php

namespace App\Policies;

class RoomStreamStrikePolicy extends HousekeepingPolicy
{
    protected function permission(): string
    {
        return 'manage_wordfilter';
    }
}

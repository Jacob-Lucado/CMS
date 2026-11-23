<?php

namespace App\Policies;

class RoomStreamPlayPolicy extends HousekeepingPolicy
{
    protected function permission(): string
    {
        return 'manage_commandlogs';
    }
}

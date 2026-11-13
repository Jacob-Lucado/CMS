<?php

namespace App\Policies;

class GameboardPolicy extends HousekeepingPolicy
{
    protected function permission(): string
    {
        return 'manage_social';
    }
}

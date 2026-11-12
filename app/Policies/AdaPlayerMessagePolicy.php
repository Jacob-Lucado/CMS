<?php

namespace App\Policies;

class AdaPlayerMessagePolicy extends HousekeepingPolicy
{
    protected function permission(): string
    {
        return 'manage_private_chatlogs';
    }
}

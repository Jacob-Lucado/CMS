<?php

namespace App\Policies;

class SecurityManagerPolicy extends HousekeepingPolicy
{
    protected function permission(): string
    {
        return 'manage_security';
    }
}

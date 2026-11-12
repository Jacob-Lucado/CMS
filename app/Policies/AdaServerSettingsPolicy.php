<?php

namespace App\Policies;

class AdaServerSettingsPolicy extends HousekeepingPolicy
{
    protected function permission(): string
    {
        return 'manage_emulator_settings';
    }
}

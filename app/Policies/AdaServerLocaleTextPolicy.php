<?php

namespace App\Policies;

class AdaServerLocaleTextPolicy extends HousekeepingPolicy
{
    protected function permission(): string
    {
        return 'manage_emulator_texts';
    }
}

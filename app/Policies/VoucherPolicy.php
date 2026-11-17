<?php

namespace App\Policies;

class VoucherPolicy extends HousekeepingPolicy
{
    protected function permission(): string
    {
        return 'manage_hotel';
    }
}

<?php

namespace App\Policies;

class HotelRoomPolicy extends HousekeepingPolicy
{
    protected function permission(): string
    {
        return 'manage_hotel';
    }
}

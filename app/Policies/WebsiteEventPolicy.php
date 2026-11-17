<?php

namespace App\Policies;

class WebsiteEventPolicy extends HousekeepingPolicy
{
    protected function permission(): string
    {
        return 'manage_social';
    }
}

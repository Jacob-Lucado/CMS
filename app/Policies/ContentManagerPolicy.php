<?php

namespace App\Policies;

class ContentManagerPolicy extends HousekeepingPolicy
{
    protected function permission(): string
    {
        return 'manage_website_content';
    }
}

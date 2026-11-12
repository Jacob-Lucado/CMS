<?php

namespace App\Policies;

class AdaCatalogItemPolicy extends HousekeepingPolicy
{
    protected function permission(): string
    {
        return 'manage_catalog_pages';
    }
}

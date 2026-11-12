<?php

namespace App\Policies;

class AdaRolePolicy extends HousekeepingPolicy
{
    protected function permission(): string
    {
        return 'manage_permissions';
    }

    protected function deletePermission(): string
    {
        return 'delete_permissions';
    }
}

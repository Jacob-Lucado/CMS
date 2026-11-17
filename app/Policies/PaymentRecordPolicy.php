<?php

namespace App\Policies;

use App\Models\User;

class PaymentRecordPolicy extends HousekeepingPolicy
{
    protected function permission(): string
    {
        return 'manage_shop';
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, mixed $record = null): bool
    {
        return false;
    }

    public function delete(User $user, mixed $record = null): bool
    {
        return false;
    }
}

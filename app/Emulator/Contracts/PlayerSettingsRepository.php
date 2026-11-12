<?php

namespace App\Emulator\Contracts;

use App\Models\User;

interface PlayerSettingsRepository
{
    public function created(User $user): void;

    public function canChangeName(User $user): bool;

    public function setCanChangeName(User $user, bool $allowed): void;
}

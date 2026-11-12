<?php

namespace App\Emulator\Data;

use App\Models\User;

final readonly class HomeFriend
{
    public function __construct(public ?User $user) {}
}

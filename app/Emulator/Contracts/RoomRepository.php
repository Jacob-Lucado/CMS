<?php

namespace App\Emulator\Contracts;

use App\Emulator\Data\RoomSummary;
use App\Models\User;
use Illuminate\Support\Collection;

interface RoomRepository
{
    /** @return Collection<int, RoomSummary> */
    public function forHome(User $user): Collection;

    public function count(): int;
}

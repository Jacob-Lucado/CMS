<?php

namespace App\Emulator\Contracts;

use App\Emulator\Data\RoomActivity;
use App\Emulator\Data\RoomSummary;
use App\Models\User;
use Illuminate\Support\Collection;

interface RoomRepository
{
    /** @return Collection<int, RoomSummary> */
    public function forHome(User $user): Collection;

    public function count(): int;

    /**
     * The fullest rooms right now. Empty on an emulator that keeps who is in a room in memory only.
     *
     * @return Collection<int, RoomActivity>
     */
    public function busiest(int $limit): Collection;
}

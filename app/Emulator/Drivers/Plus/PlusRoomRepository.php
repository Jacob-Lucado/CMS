<?php

namespace App\Emulator\Drivers\Plus;

use App\Emulator\Contracts\RoomRepository;
use App\Emulator\Data\RoomActivity;
use App\Emulator\Data\RoomSummary;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

final class PlusRoomRepository implements RoomRepository
{
    public function forHome(User $user): Collection
    {
        return DB::table('rooms')->where('owner', $user->id)->get(['id', 'caption', 'description', 'state'])
            ->map(fn (object $room) => new RoomSummary((int) $room->id, (string) $room->caption, (string) $room->description, (string) $room->state));
    }

    public function count(): int
    {
        return DB::table('rooms')->count();
    }

    public function busiest(int $limit): Collection
    {
        return DB::table('rooms')
            ->leftJoin('users', 'users.id', '=', 'rooms.owner')
            ->where('rooms.users_now', '>', 0)
            ->orderByDesc('rooms.users_now')
            ->limit($limit)
            ->get(['rooms.id', 'rooms.caption', 'rooms.users_now', 'users.username'])
            ->map(fn (object $room) => new RoomActivity((int) $room->id, (string) $room->caption, (string) ($room->username ?? ''), (int) $room->users_now));
    }
}

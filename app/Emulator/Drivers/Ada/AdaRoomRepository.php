<?php

namespace App\Emulator\Drivers\Ada;

use App\Emulator\Contracts\RoomRepository;
use App\Emulator\Data\RoomSummary;
use App\Models\Ada\AdaRoom;
use App\Models\User;
use Illuminate\Support\Collection;

class AdaRoomRepository implements RoomRepository
{
    /**
     * Ada keeps the door policy in room_settings.access_type as a
     * RoomAccessType ordinal. Mapping it onto the vocabulary Arcturus stores
     * inline lets the home page badge a locked room the same way on both.
     */
    private const ACCESS_TYPES = [
        0 => 'open',
        1 => 'locked',
        2 => 'password',
        3 => 'invisible',
    ];

    public function forHome(User $user): Collection
    {
        return AdaRoom::query()
            ->where('rooms.owner_id', $user->id)
            ->leftJoin('room_settings', 'room_settings.room_id', '=', 'rooms.id')
            ->get(['rooms.id', 'rooms.name', 'rooms.description', 'room_settings.access_type'])
            ->map(fn (AdaRoom $room): RoomSummary => new RoomSummary(
                (int) $room->id,
                (string) $room->name,
                (string) $room->description,
                self::ACCESS_TYPES[(int) $room->getAttribute('access_type')] ?? 'open',
            ));
    }

    public function count(): int
    {
        return AdaRoom::query()->count();
    }
}

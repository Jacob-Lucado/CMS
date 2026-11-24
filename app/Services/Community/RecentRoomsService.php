<?php

namespace App\Services\Community;

use App\Emulator\Emulator;
use App\Models\HotelRoom;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class RecentRoomsService
{
    public const LIMIT = 6;

    /**
     * The open rooms a player entered most recently, newest first.
     *
     * @return Collection<int, array{id: int, name: string, owner: string, users: int}>
     */
    public function forUser(User $user): Collection
    {
        if (Emulator::driver() !== 'plus' || ! Schema::hasTable('user_roomvisits')) {
            return collect();
        }

        $visits = DB::table('user_roomvisits')
            ->where('user_id', $user->id)
            ->selectRaw('room_id, MAX(entry_timestamp) as last_entry')
            ->groupBy('room_id')
            ->orderByDesc('last_entry')
            ->limit(self::LIMIT * 3)
            ->pluck('last_entry', 'room_id');

        $rooms = HotelRoom::query()
            ->with('ownerUser:id,username')
            ->whereIn('id', $visits->keys())
            ->where('state', 'open')
            ->get()
            ->keyBy('id');

        $recent = [];

        foreach ($visits as $roomId => $lastEntry) {
            $room = $rooms->get($roomId);

            if ($room === null || count($recent) >= self::LIMIT) {
                continue;
            }

            $recent[] = [
                'id' => (int) $room->id,
                'name' => (string) $room->caption,
                'owner' => (string) ($room->ownerUser?->username ?? ''),
                'users' => (int) $room->users_now,
            ];
        }

        return collect($recent);
    }
}

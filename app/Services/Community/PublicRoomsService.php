<?php

namespace App\Services\Community;

use App\Emulator\Emulator;
use App\Models\Game\PlusNavigatorPublic;
use App\Models\HotelRoom;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

class PublicRoomsService
{
    /**
     * The rooms featured in the Public tab of the navigator, in navigator order.
     *
     * @return Collection<int, array{id: int, name: string, description: string, image: string, users: int, max: int}>
     */
    public function rooms(): Collection
    {
        if (Emulator::driver() !== 'plus' || ! Schema::hasTable('navigator_publics')) {
            return collect();
        }

        $rooms = HotelRoom::query()
            ->whereIn('id', PlusNavigatorPublic::query()->where('enabled', true)->select('room_id'))
            ->get(['id', 'users_now', 'users_max'])
            ->keyBy('id');

        $listed = [];

        foreach (PlusNavigatorPublic::query()->where('enabled', true)->orderBy('order_num')->orderBy('room_id')->get() as $public) {
            $room = $rooms->get($public->room_id);

            if ($room === null) {
                continue;
            }

            $listed[] = [
                'id' => $public->room_id,
                'name' => $public->caption,
                'description' => $public->description,
                'image' => $public->image_url,
                'users' => (int) $room->users_now,
                'max' => (int) $room->users_max,
            ];
        }

        return collect($listed);
    }
}

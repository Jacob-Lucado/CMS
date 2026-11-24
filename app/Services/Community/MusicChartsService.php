<?php

namespace App\Services\Community;

use App\Emulator\Emulator;
use App\Models\HotelRoom;
use App\Models\RoomStreamPlay;
use Illuminate\Support\Facades\Schema;

class MusicChartsService
{
    public const DAYS = 7;

    /**
     * @return array{available: bool, songs: array, djs: array, recent: array}
     */
    public function charts(): array
    {
        if (! Schema::hasTable('room_stream_plays')) {
            return ['available' => false, 'songs' => [], 'djs' => [], 'recent' => []];
        }

        $since = now()->subDays(self::DAYS);

        $songs = RoomStreamPlay::query()
            ->where('played_at', '>=', $since)
            ->selectRaw('url, kind, COUNT(*) as plays')
            ->groupBy('url', 'kind')
            ->orderByDesc('plays')
            ->orderBy('url')
            ->limit(10)
            ->get();

        $djs = RoomStreamPlay::query()
            ->where('played_at', '>=', $since)
            ->selectRaw('set_by, COUNT(*) as plays')
            ->groupBy('set_by')
            ->orderByDesc('plays')
            ->orderBy('set_by')
            ->limit(10)
            ->get();

        $recent = RoomStreamPlay::query()
            ->orderByDesc('played_at')
            ->orderByDesc('id')
            ->limit(15)
            ->get();

        $captions = Emulator::driver() === 'plus' ? HotelRoom::query()->whereIn('id', $recent->pluck('room_id'))->pluck('caption', 'id') : collect();
        $recent->each(fn (RoomStreamPlay $play) => $play->setAttribute('room_name', $captions->get($play->room_id)));

        return [
            'available' => true,
            'songs' => $songs->all(),
            'djs' => $djs->all(),
            'recent' => $recent->all(),
        ];
    }
}

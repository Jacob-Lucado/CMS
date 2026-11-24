<?php

namespace App\Http\Controllers\Community;

use App\Http\Controllers\Controller;
use App\Services\Community\MusicChartsService;
use Illuminate\View\View;

class RoomMusicHistoryController extends Controller
{
    public function __invoke(int $roomId, MusicChartsService $charts): View
    {
        abort_unless($charts->hasPlays($roomId), 404);

        return view('music-room', [
            'roomName' => $charts->roomName($roomId),
            'plays' => $charts->roomHistory($roomId),
        ]);
    }
}

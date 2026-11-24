<?php

namespace App\Http\Controllers\Community;

use App\Http\Controllers\Controller;
use App\Models\FeaturedRoom;
use App\Models\HotelRoom;
use App\Models\User;
use App\Services\Community\PublicRoomsService;
use App\Services\Community\RecentRoomsService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RoomBrowserController extends Controller
{
    public function __invoke(Request $request, PublicRoomsService $publicRooms, RecentRoomsService $recentRooms): View
    {
        $search = trim((string) $request->query('q', ''));
        $sort = $request->query('sort') === 'likes' ? 'score' : 'users_now';

        $rooms = HotelRoom::query()
            ->with('ownerUser:id,username,look')
            ->where('state', 'open')
            ->when($search !== '', function ($query) use ($search) {
                $like = '%' . addcslashes($search, '%_\\') . '%';
                $query->where(function ($query) use ($like) {
                    $query->where('caption', 'like', $like)
                        ->orWhereHas('ownerUser', fn ($owner) => $owner->where('username', 'like', $like));
                });
            })
            ->orderByDesc($sort)
            ->orderByDesc('id')
            ->paginate(24)
            ->withQueryString();

        $featured = FeaturedRoom::query()
            ->with('room.ownerUser:id,username')
            ->whereHas('room', fn ($query) => $query->where('state', 'open'))
            ->orderByDesc('room_of_the_week')->orderBy('position')->orderByDesc('id')
            ->limit(7)->get();

        $viewer = $request->user();

        return view('rooms', [
            'recentRooms' => $viewer instanceof User ? $recentRooms->forUser($viewer) : collect(),
            'publicRooms' => $publicRooms->rooms(),
            'rooms' => $rooms,
            'search' => $search,
            'sort' => $sort,
            'weekly' => $featured->firstWhere('room_of_the_week', true),
            'picks' => $featured->where('room_of_the_week', false)->values(),
        ]);
    }
}

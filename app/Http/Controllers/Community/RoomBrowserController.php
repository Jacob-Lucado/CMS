<?php

namespace App\Http\Controllers\Community;

use App\Http\Controllers\Controller;
use App\Models\HotelRoom;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RoomBrowserController extends Controller
{
    public function __invoke(Request $request): View
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

        return view('rooms', ['rooms' => $rooms, 'search' => $search, 'sort' => $sort]);
    }
}

<?php

namespace App\Http\Controllers\Community;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Community\CommunityReadService;
use App\Services\Community\WeeklyLeaderboardService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LeaderboardController extends Controller
{
    public function __invoke(Request $request, CommunityReadService $community, WeeklyLeaderboardService $weekly): View
    {
        $viewer = $request->user();

        return view('leaderboard', [
            ...$community->leaderboards(),
            ...$weekly->current(),
            'myStanding' => $viewer instanceof User ? $weekly->standingFor($viewer) : null,
        ]);
    }
}

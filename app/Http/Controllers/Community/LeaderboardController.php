<?php

namespace App\Http\Controllers\Community;

use App\Http\Controllers\Controller;
use App\Services\Community\CommunityReadService;
use App\Services\Community\WeeklyLeaderboardService;
use Illuminate\View\View;

class LeaderboardController extends Controller
{
    public function __invoke(CommunityReadService $community, WeeklyLeaderboardService $weekly): View
    {
        return view('leaderboard', [...$community->leaderboards(), ...$weekly->current()]);
    }
}

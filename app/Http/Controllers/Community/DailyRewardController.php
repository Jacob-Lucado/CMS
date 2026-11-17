<?php

namespace App\Http\Controllers\Community;

use App\Http\Controllers\Controller;
use App\Models\DailyReward;
use App\Services\Community\DailyRewardService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DailyRewardController extends Controller
{
    public function show(Request $request, DailyRewardService $rewards): View
    {
        $status = $rewards->status($request->user());

        return view('daily-reward', [
            'reward' => DailyReward::query()->firstOrNew(['user_id' => $request->user()->id]),
            'claimed' => $status['claimed'],
            'streak' => $status['streak'],
            'amount' => $status['amount'],
        ]);
    }

    public function claim(Request $request, DailyRewardService $rewards): RedirectResponse
    {
        return back()->with('status', $rewards->claim($request->user())['message']);
    }
}

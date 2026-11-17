<?php

namespace App\Http\Controllers\Community;

use App\Actions\SendCurrency;
use App\Enums\CurrencyTypes;
use App\Exceptions\CurrencyGrantException;
use App\Http\Controllers\Controller;
use App\Models\DailyReward;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DailyRewardController extends Controller
{
    public function show(Request $request): View
    {
        $reward = DailyReward::query()->firstOrNew(['user_id' => $request->user()->id]);
        $claimed = $reward->claimedToday();
        $streak = $claimed || $reward->last_claimed_on?->isYesterday() ? $reward->streak : 0;

        return view('daily-reward', [
            'reward' => $reward,
            'claimed' => $claimed,
            'streak' => $streak,
            'amount' => DailyReward::amountFor($streak + 1),
        ]);
    }

    public function claim(Request $request, SendCurrency $currency): RedirectResponse
    {
        $user = $request->user();

        return DB::transaction(function () use ($user, $currency): RedirectResponse {
            DailyReward::query()->firstOrCreate(['user_id' => $user->id]);
            $reward = DailyReward::query()->where('user_id', $user->id)->lockForUpdate()->firstOrFail();

            if ($reward->claimedToday()) {
                return back()->with('status', __('You already claimed today. Come back tomorrow.'));
            }

            $streak = $reward->nextStreak();
            $amount = DailyReward::amountFor($streak);

            try {
                $currency->execute($user, CurrencyTypes::Credits, $amount);
            } catch (CurrencyGrantException) {
                return back()->with('status', __('We could not send your reward while you are in the hotel. Leave the hotel and try again.'));
            }

            $reward->update([
                'streak' => $streak,
                'best_streak' => max($reward->best_streak, $streak),
                'total_claims' => $reward->total_claims + 1,
                'last_claimed_on' => today(),
            ]);

            return back()->with('status', __('You got :amount credits. Streak: :days.', ['amount' => $amount, 'days' => $streak]));
        });
    }
}

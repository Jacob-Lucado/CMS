<?php

namespace App\Services\Community;

use App\Actions\SendCurrency;
use App\Enums\CurrencyTypes;
use App\Exceptions\CurrencyGrantException;
use App\Models\DailyReward;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class DailyRewardService
{
    public function __construct(private readonly SendCurrency $currency) {}

    /** @return array{claimed: bool, streak: int, best_streak: int, total_claims: int, amount: int} */
    public function status(User $user): array
    {
        $reward = DailyReward::query()->firstOrNew(['user_id' => $user->id]);
        $claimed = $reward->claimedToday();
        $streak = $claimed || $reward->last_claimed_on?->isYesterday() ? $reward->streak : 0;

        return [
            'claimed' => $claimed,
            'streak' => $streak,
            'best_streak' => (int) $reward->best_streak,
            'total_claims' => (int) $reward->total_claims,
            'amount' => DailyReward::amountFor($streak + 1),
        ];
    }

    /** @return array{ok: bool, message: string} */
    public function claim(User $user): array
    {
        return DB::transaction(function () use ($user): array {
            DailyReward::query()->firstOrCreate(['user_id' => $user->id]);
            $reward = DailyReward::query()->where('user_id', $user->id)->lockForUpdate()->firstOrFail();

            if ($reward->claimedToday()) {
                return ['ok' => false, 'message' => __('You already claimed today. Come back tomorrow.')];
            }

            $streak = $reward->nextStreak();
            $amount = DailyReward::amountFor($streak);

            try {
                $this->currency->execute($user, CurrencyTypes::Credits, $amount);
            } catch (CurrencyGrantException) {
                return ['ok' => false, 'message' => __('We could not send your reward while you are in the hotel. Leave the hotel and try again.')];
            }

            $reward->update([
                'streak' => $streak,
                'best_streak' => max($reward->best_streak, $streak),
                'total_claims' => $reward->total_claims + 1,
                'last_claimed_on' => today(),
            ]);

            return ['ok' => true, 'message' => __('You got :amount credits. Streak: :days.', ['amount' => $amount, 'days' => $streak])];
        });
    }
}

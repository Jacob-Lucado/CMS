<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $user_id
 * @property int $streak
 * @property int $best_streak
 * @property int $total_claims
 * @property \Illuminate\Support\Carbon|null $last_claimed_on
 */
class DailyReward extends Model
{
    protected $table = 'website_daily_rewards';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['last_claimed_on' => 'date'];
    }

    public function claimedToday(): bool
    {
        return $this->last_claimed_on?->isToday() ?? false;
    }

    /** The streak a claim made now would reach. */
    public function nextStreak(): int
    {
        if ($this->claimedToday()) {
            return $this->streak;
        }

        return $this->last_claimed_on?->isYesterday() ? $this->streak + 1 : 1;
    }

    /** Credits for reaching a given streak day. */
    public static function amountFor(int $streak): int
    {
        return (int) setting('daily_reward_credits', 50) + (int) setting('daily_reward_streak_bonus', 10) * min(max($streak, 1) - 1, 6);
    }
}

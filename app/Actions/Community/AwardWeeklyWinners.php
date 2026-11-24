<?php

namespace App\Actions\Community;

use App\Contracts\Rcon;
use App\Emulator\Contracts\BadgeRepository;
use App\Exceptions\RconConnectionException;
use App\Models\Community\WeeklyWinner;
use App\Models\Social\WebsiteNotification;
use App\Models\User;
use App\Services\Community\WeeklyLeaderboardService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * Closes a leaderboard week: records who topped each board and hands out the badge set for it.
 */
class AwardWeeklyWinners
{
    public function __construct(
        private readonly WeeklyLeaderboardService $leaderboards,
        private readonly BadgeRepository $badges,
        private readonly Rcon $rcon,
    ) {}

    /**
     * @return Collection<int, WeeklyWinner> The winners recorded by this call.
     */
    public function forWeek(Carbon $weekStart): Collection
    {
        $awarded = collect();

        foreach (WeeklyLeaderboardService::BOARDS as $board) {
            if (WeeklyWinner::query()->whereDate('week_start', $weekStart)->where('board', $board)->exists()) {
                continue;
            }

            $top = $this->leaderboards->standings($board, $weekStart, 1)->first();

            if ($top === null || $top->value < 1) {
                continue;
            }

            $badge = trim((string) setting("weekly_{$board}_badge"));

            $winner = WeeklyWinner::query()->create([
                'week_start' => $weekStart->toDateString(),
                'board' => $board,
                'user_id' => $top->user->id,
                'score' => $top->value,
                'badge' => $badge !== '' ? $badge : null,
            ]);

            if ($badge !== '') {
                $this->giveBadge($top->user, $badge);
            }

            WebsiteNotification::query()->create([
                'user_id' => $top->user->id,
                'type' => 'weekly_winner',
                'message' => $board === 'music'
                    ? __('You played the most songs last week. Nice work!')
                    : __('You got the most kudos last week. Nice work!'),
                'url' => route('leaderboard.index', absolute: false),
            ]);

            $awarded->push($winner);
        }

        return $awarded;
    }

    private function giveBadge(User $user, string $badge): void
    {
        $this->badges->define($badge);

        try {
            $this->rcon->giveBadge($user, $badge);
        } catch (RconConnectionException) {
            $this->badges->grant($user, $badge);
        }
    }
}

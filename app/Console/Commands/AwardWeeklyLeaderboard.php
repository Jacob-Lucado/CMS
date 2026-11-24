<?php

namespace App\Console\Commands;

use App\Actions\Community\AwardWeeklyWinners;
use App\Services\Community\WeeklyLeaderboardService;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class AwardWeeklyLeaderboard extends Command
{
    protected $signature = 'leaderboard:award {--week= : A date in the week to close, defaults to last week}';

    protected $description = 'Record last week\'s leaderboard winners and give them their badge';

    public function handle(WeeklyLeaderboardService $leaderboards, AwardWeeklyWinners $awards): int
    {
        $at = $this->option('week') ? Carbon::parse((string) $this->option('week')) : now()->subWeek();
        $start = $leaderboards->weekStart($at);

        $winners = $awards->forWeek($start);

        $this->info(trans_choice('Closed the week of :date with :count winner.|Closed the week of :date with :count winners.', $winners->count(), ['date' => $start->toDateString()]));

        return self::SUCCESS;
    }
}

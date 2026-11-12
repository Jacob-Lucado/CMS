<?php

namespace App\Emulator\Drivers\Ada;

use App\Emulator\Contracts\PlayerStatsRepository;
use App\Emulator\Data\Stat;
use App\Emulator\Support\LeaderboardEntries;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Ada spreads statistics over player_data (achievement score) and
 * player_respects. It does not persist session duration at all, so the
 * online-time ranking is reported as unsupported instead of fabricated.
 */
class AdaPlayerStatsRepository implements PlayerStatsRepository
{
    public function supports(Stat $stat): bool
    {
        return $stat !== Stat::OnlineTime;
    }

    public function topBy(Stat $stat, int $limit, array $excludeUserIds = []): Collection
    {
        if (! $this->supports($stat)) {
            return collect();
        }

        $scores = $stat === Stat::RespectsReceived
            ? DB::table('player_respects')
                ->selectRaw('target_player_id as player_id, COUNT(*) as score')
                ->whereNotIn('target_player_id', $excludeUserIds)
                ->groupBy('target_player_id')
                ->orderByDesc('score')
                ->limit($limit)
                ->pluck('score', 'player_id')
            : DB::table('player_data')
                ->whereNotIn('player_id', $excludeUserIds)
                ->orderByDesc('achievement_score')
                ->limit($limit)
                ->pluck('achievement_score', 'player_id');

        /** @var array<int, int> $ranked */
        $ranked = $scores->map(fn ($score): int => (int) $score)->all();

        return LeaderboardEntries::forUsers($ranked);
    }
}

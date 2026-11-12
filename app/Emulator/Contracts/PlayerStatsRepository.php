<?php

namespace App\Emulator\Contracts;

use App\Emulator\Data\LeaderboardEntry;
use App\Emulator\Data\Stat;
use Illuminate\Support\Collection;

/**
 * Reads player statistics (online time, respects, achievement score) that each
 * emulator stores in its own way - Arcturus in users_settings and Ada across
 * player_data and player_respects.
 */
interface PlayerStatsRepository
{
    /**
     * Whether the emulator persists this statistic at all. Rankings for
     * unsupported statistics are hidden instead of rendered empty.
     */
    public function supports(Stat $stat): bool;

    /**
     * The highest-ranked players by a statistic, highest first.
     *
     * @param  array<int, int>  $excludeUserIds
     *
     * @return Collection<int, LeaderboardEntry>
     */
    public function topBy(Stat $stat, int $limit, array $excludeUserIds = []): Collection;
}

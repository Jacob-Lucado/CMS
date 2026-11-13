<?php

namespace App\Emulator\Drivers\Ada;

use App\Emulator\Contracts\ScoreboardRepository;
use Illuminate\Support\Collection;

/**
 * Ada keeps no highscore boards the website can read.
 */
final class AdaScoreboardRepository implements ScoreboardRepository
{
    public function scoreboards(int $limit = 200): Collection
    {
        return collect();
    }

    public function find(int $itemId): null
    {
        return null;
    }

    public function top(int $itemId, int $limit = 5): Collection
    {
        return collect();
    }
}

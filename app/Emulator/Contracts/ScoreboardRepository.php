<?php

namespace App\Emulator\Contracts;

use App\Emulator\Data\GameScore;
use App\Emulator\Data\Scoreboard;
use Illuminate\Support\Collection;

interface ScoreboardRepository
{
    /**
     * Highscore furni placed in rooms.
     *
     * @return Collection<int, Scoreboard>
     */
    public function scoreboards(int $limit = 200): Collection;

    public function find(int $itemId): ?Scoreboard;

    /**
     * The best scores on a board, highest first.
     *
     * @return Collection<int, GameScore>
     */
    public function top(int $itemId, int $limit = 5): Collection;
}

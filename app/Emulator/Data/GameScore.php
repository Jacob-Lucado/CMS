<?php

namespace App\Emulator\Data;

/**
 * One row of a highscore board.
 */
final readonly class GameScore
{
    /** @param  list<string>  $players  usernames */
    public function __construct(
        public int $score,
        public array $players,
    ) {}
}

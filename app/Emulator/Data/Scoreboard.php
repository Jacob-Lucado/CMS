<?php

namespace App\Emulator\Data;

/**
 * A highscore furni placed in a room.
 */
final readonly class Scoreboard
{
    public function __construct(
        public int $itemId,
        public string $itemName,
        public int $roomId,
        public string $roomName,
    ) {}
}

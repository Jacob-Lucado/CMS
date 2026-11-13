<?php

namespace App\Emulator\Data;

/**
 * A room with players in it right now, normalised across emulators.
 */
final readonly class RoomActivity
{
    public function __construct(
        public int $id,
        public string $name,
        public string $ownerName,
        public int $playersInside,
    ) {}
}

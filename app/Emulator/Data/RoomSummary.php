<?php

namespace App\Emulator\Data;

/**
 * One of a player's rooms, normalised across emulators.
 *
 * $state uses Arcturus' vocabulary - open, locked, password or invisible -
 * because that is what the home page already styles. Drivers storing the door
 * policy differently map onto it.
 */
final readonly class RoomSummary
{
    public function __construct(
        public int $id,
        public string $name,
        public string $description,
        public string $state,
    ) {}
}

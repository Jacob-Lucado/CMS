<?php

namespace App\Emulator\Data;

use App\Models\User;

/**
 * A single ranked player, normalised across emulators so the leaderboard view
 * does not care where the value came from.
 */
final readonly class LeaderboardEntry
{
    public function __construct(
        public User $user,
        public int $value,
    ) {}
}

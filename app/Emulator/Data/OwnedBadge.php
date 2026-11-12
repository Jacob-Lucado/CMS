<?php

namespace App\Emulator\Data;

/**
 * A badge a player owns, normalised across emulators. The snake_case property
 * name matches what the badge widgets already render.
 */
final readonly class OwnedBadge
{
    public function __construct(
        public string $badge_code,
        public int $slot,
    ) {}
}

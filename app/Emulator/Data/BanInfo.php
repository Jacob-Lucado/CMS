<?php

namespace App\Emulator\Data;

/**
 * An active ban, normalised across emulators. The snake_case property names
 * match what the banned page already renders, whichever driver produced it.
 *
 * A null expiry means the ban never lifts. Ada stores that as NULL; Arcturus
 * has no such notion and always carries a timestamp.
 */
final readonly class BanInfo
{
    public function __construct(
        public string $type,
        public string $ban_reason,
        public ?int $ban_expire,
    ) {}
}

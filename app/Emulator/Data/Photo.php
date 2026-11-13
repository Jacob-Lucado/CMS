<?php

namespace App\Emulator\Data;

use Carbon\CarbonInterface;

/**
 * A camera photo, normalised across emulators. The id is text because some emulators use UUIDs.
 */
final readonly class Photo
{
    public function __construct(
        public string $id,
        public string $url,
        public int $userId,
        public ?CarbonInterface $takenAt,
    ) {}
}

<?php

namespace App\Emulator\Data;

use Carbon\CarbonInterface;

/**
 * A Marketplace listing, normalised across emulators.
 */
final readonly class MarketOffer
{
    public function __construct(
        public int $id,
        public string $name,
        public string $itemName,
        public int $price,
        public int $sellerId,
        public ?CarbonInterface $listedAt,
        public bool $sold,
        public int $limitedNumber = 0,
        public int $limitedStack = 0,
    ) {}
}

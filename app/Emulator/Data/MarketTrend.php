<?php

namespace App\Emulator\Data;

/**
 * How one furni sells on the Marketplace.
 */
final readonly class MarketTrend
{
    public function __construct(
        public string $name,
        public string $itemName,
        public int $sold,
        public int $averagePrice,
    ) {}
}

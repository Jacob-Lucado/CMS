<?php

namespace App\Emulator\Data;

/**
 * One thing for sale on a catalog page, with its prices and icon.
 */
final readonly class CatalogOffer
{
    public function __construct(
        public int $id,
        public string $name,
        public int $cost_credits,
        public int $cost_points,
        public int $points_type,
        public ?string $icon,
    ) {}
}

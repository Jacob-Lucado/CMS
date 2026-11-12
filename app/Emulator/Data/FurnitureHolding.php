<?php

namespace App\Emulator\Data;

/**
 * How many copies of one base furniture item a player holds. The snake_case
 * property names match the rare-values view.
 */
final readonly class FurnitureHolding
{
    public function __construct(
        public int $user_id,
        public int $item_count,
    ) {}
}

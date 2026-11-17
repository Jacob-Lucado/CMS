<?php

namespace App\Emulator\Data;

/**
 * One furniture definition as shown on the public furni browser.
 */
final readonly class FurnitureDefinition
{
    public function __construct(
        public int $id,
        public string $item_name,
        public string $public_name,
        public string $type,
        public int $width,
        public int $length,
        public bool $can_trade,
    ) {}
}

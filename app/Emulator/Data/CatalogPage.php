<?php

namespace App\Emulator\Data;

/**
 * A catalog page as listed on the public catalog browser.
 */
final readonly class CatalogPage
{
    public function __construct(
        public int $id,
        public ?int $parent_id,
        public string $caption,
    ) {}
}

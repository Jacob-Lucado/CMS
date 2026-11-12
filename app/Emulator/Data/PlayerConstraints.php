<?php

namespace App\Emulator\Data;

final readonly class PlayerConstraints
{
    public function __construct(
        public int $usernameLength,
        public int $emailLength,
        public int $mottoLength,
        public int $figureLength,
    ) {}
}

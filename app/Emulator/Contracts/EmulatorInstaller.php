<?php

namespace App\Emulator\Contracts;

use Illuminate\Console\Command;

interface EmulatorInstaller
{
    public function prepare(Command $command): bool;
}

<?php

namespace Tests\Fixtures;

use App\Emulator\Contracts\EmulatorInstaller;
use Illuminate\Console\Command;

class ThirdEmulatorInstaller implements EmulatorInstaller
{
    public function prepare(Command $command): bool
    {
        return true;
    }
}

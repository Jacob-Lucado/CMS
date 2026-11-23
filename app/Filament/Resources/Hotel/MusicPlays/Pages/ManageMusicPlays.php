<?php

namespace App\Filament\Resources\Hotel\MusicPlays\Pages;

use App\Filament\Resources\Hotel\MusicPlays\MusicPlayResource;
use Filament\Resources\Pages\ManageRecords;

class ManageMusicPlays extends ManageRecords
{
    protected static string $resource = MusicPlayResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}

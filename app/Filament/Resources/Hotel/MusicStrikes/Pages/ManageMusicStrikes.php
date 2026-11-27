<?php

namespace App\Filament\Resources\Hotel\MusicStrikes\Pages;

use App\Filament\Resources\Hotel\MusicStrikes\MusicStrikeResource;
use Filament\Resources\Pages\ManageRecords;

class ManageMusicStrikes extends ManageRecords
{
    protected static string $resource = MusicStrikeResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}

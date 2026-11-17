<?php

namespace App\Filament\Resources\Website\HallOfFameEntries\Pages;

use App\Filament\Resources\Website\HallOfFameEntries\HallOfFameEntryResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageHallOfFameEntries extends ManageRecords
{
    protected static string $resource = HallOfFameEntryResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}

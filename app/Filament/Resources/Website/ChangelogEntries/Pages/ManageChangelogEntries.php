<?php

namespace App\Filament\Resources\Website\ChangelogEntries\Pages;

use App\Filament\Resources\Website\ChangelogEntries\ChangelogEntryResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageChangelogEntries extends ManageRecords
{
    protected static string $resource = ChangelogEntryResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}

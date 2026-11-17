<?php

namespace App\Filament\Resources\Website\WebsiteRareValues\Pages;

use App\Filament\Resources\Website\WebsiteRareValues\WebsiteRareValueResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageWebsiteRareValues extends ManageRecords
{
    protected static string $resource = WebsiteRareValueResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}

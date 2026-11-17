<?php

namespace App\Filament\Resources\Website\WebsiteRiddles\Pages;

use App\Filament\Resources\Website\WebsiteRiddles\WebsiteRiddleResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageWebsiteRiddles extends ManageRecords
{
    protected static string $resource = WebsiteRiddleResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}

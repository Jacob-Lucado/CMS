<?php

namespace App\Filament\Resources\Website\WebsitePolls\Pages;

use App\Filament\Resources\Website\WebsitePolls\WebsitePollResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageWebsitePolls extends ManageRecords
{
    protected static string $resource = WebsitePollResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}

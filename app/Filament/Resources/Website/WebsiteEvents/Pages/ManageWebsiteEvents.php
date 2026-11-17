<?php

namespace App\Filament\Resources\Website\WebsiteEvents\Pages;

use App\Filament\Resources\Website\WebsiteEvents\WebsiteEventResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageWebsiteEvents extends ManageRecords
{
    protected static string $resource = WebsiteEventResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}

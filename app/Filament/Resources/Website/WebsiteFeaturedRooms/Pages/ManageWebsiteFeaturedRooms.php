<?php

namespace App\Filament\Resources\Website\WebsiteFeaturedRooms\Pages;

use App\Filament\Resources\Website\WebsiteFeaturedRooms\WebsiteFeaturedRoomResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageWebsiteFeaturedRooms extends ManageRecords
{
    protected static string $resource = WebsiteFeaturedRoomResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}

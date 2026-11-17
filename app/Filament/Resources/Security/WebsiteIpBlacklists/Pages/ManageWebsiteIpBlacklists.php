<?php

namespace App\Filament\Resources\Security\WebsiteIpBlacklists\Pages;

use App\Filament\Resources\Security\WebsiteIpBlacklists\WebsiteIpBlacklistResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageWebsiteIpBlacklists extends ManageRecords
{
    protected static string $resource = WebsiteIpBlacklistResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}

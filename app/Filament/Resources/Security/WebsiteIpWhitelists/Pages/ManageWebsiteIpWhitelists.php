<?php

namespace App\Filament\Resources\Security\WebsiteIpWhitelists\Pages;

use App\Filament\Resources\Security\WebsiteIpWhitelists\WebsiteIpWhitelistResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageWebsiteIpWhitelists extends ManageRecords
{
    protected static string $resource = WebsiteIpWhitelistResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}

<?php

namespace App\Filament\Resources\Security\WebsiteBetaCodes\Pages;

use App\Filament\Resources\Security\WebsiteBetaCodes\WebsiteBetaCodeResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageWebsiteBetaCodes extends ManageRecords
{
    protected static string $resource = WebsiteBetaCodeResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}

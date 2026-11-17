<?php

namespace App\Filament\Resources\Website\WebsiteRules\Pages;

use App\Filament\Resources\Website\WebsiteRules\WebsiteRuleResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageWebsiteRules extends ManageRecords
{
    protected static string $resource = WebsiteRuleResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}

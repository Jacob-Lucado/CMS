<?php

namespace App\Filament\Resources\Website\WebsiteRuleCategories\Pages;

use App\Filament\Resources\Website\WebsiteRuleCategories\WebsiteRuleCategoryResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageWebsiteRuleCategories extends ManageRecords
{
    protected static string $resource = WebsiteRuleCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}

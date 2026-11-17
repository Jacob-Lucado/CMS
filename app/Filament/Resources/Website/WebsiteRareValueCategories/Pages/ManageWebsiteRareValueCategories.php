<?php

namespace App\Filament\Resources\Website\WebsiteRareValueCategories\Pages;

use App\Filament\Resources\Website\WebsiteRareValueCategories\WebsiteRareValueCategoryResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageWebsiteRareValueCategories extends ManageRecords
{
    protected static string $resource = WebsiteRareValueCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}

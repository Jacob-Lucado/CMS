<?php

namespace App\Filament\Resources\Hotel\Social\Pages;

use App\Filament\Resources\Hotel\Social\GameboardResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageGameboards extends ManageRecords
{
    protected static string $resource = GameboardResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()->mutateDataUsing(GameboardResource::withRoom(...))];
    }
}

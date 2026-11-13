<?php

namespace App\Filament\Resources\Hotel\Social\Pages;

use App\Filament\Resources\Hotel\Social\PublicRoomResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManagePublicRooms extends ManageRecords
{
    protected static string $resource = PublicRoomResource::class;

    protected ?string $subheading = 'Rooms listed here show in the Public tab of the in-game navigator.';

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()->label(__('List a room'))->after(fn () => PublicRoomResource::reloadNavigator())];
    }
}

<?php

namespace App\Filament\Resources\Hotel\MusicBans\Pages;

use App\Filament\Resources\Hotel\MusicBans\MusicBanResource;
use App\Models\RoomStreamBan;
use Filament\Actions\CreateAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ManageRecords;

class ManageMusicBans extends ManageRecords
{
    protected static string $resource = MusicBanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label(__('Ban a link'))
                ->mutateDataUsing(function (array $data): array {
                    $data['ban_key'] = RoomStreamBan::keyFor($data['url']);
                    $data['banned_by'] = (string) auth()->user()?->username;
                    $data['reason'] ??= '';

                    return $data;
                })
                ->before(function (CreateAction $action, array $data): void {
                    // The unique key would fail the insert, so say it kindly first.
                    if (RoomStreamBan::query()->where('ban_key', RoomStreamBan::keyFor($data['url']))->exists()) {
                        Notification::make()->title(__('That link is already banned.'))->warning()->send();
                        $action->halt();
                    }
                }),
        ];
    }
}

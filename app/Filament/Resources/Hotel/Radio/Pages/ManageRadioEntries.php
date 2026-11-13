<?php

namespace App\Filament\Resources\Hotel\Radio\Pages;

use App\Emulator\Contracts\RankRepository;
use App\Filament\Resources\Hotel\Radio\RadioEntryResource;
use App\Services\Radio\RadioAccess;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ManageRecords;

class ManageRadioEntries extends ManageRecords
{
    protected static string $resource = RadioEntryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('settings')
                ->label(__('DJs and suggestions'))
                ->icon('heroicon-o-cog-6-tooth')
                ->modalHeading(__('Who runs the radio'))
                ->modalDescription(__('DJs add and remove things, review suggestions and pick what\'s on air. The hotel\'s top rank is always a DJ.'))
                ->fillForm(fn (): array => [
                    'roles' => app(RadioAccess::class)->djRoles(),
                    'submissions' => app(RadioAccess::class)->submissionsOpen(),
                ])
                ->schema([
                    Select::make('roles')
                        ->label(__('DJ roles'))
                        ->multiple()
                        ->options(fn (): array => app(RankRepository::class)->roleOptions())
                        ->helperText(__('Anyone who holds one of these roles is a DJ.')),
                    Toggle::make('submissions')
                        ->label(__('Let players suggest stations and tracks')),
                ])
                ->action(function (array $data): void {
                    app(RadioAccess::class)->configure(array_values($data['roles'] ?? []), (bool) $data['submissions']);

                    Notification::make()->success()->title(__('Radio settings saved'))->send();
                }),
        ];
    }
}

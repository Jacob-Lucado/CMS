<?php

namespace App\Filament\Resources\Hotel\Social\Pages;

use App\Filament\Resources\Hotel\Social\SocialReportResource;
use App\Models\User;
use App\Services\Social\Notifier;
use Filament\Actions\Action;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ManageRecords;
use Filament\Schemas\Components\Utilities\Get;

class ManageSocialReports extends ManageRecords
{
    protected static string $resource = SocialReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('notify')
                ->label(__('Send a notification'))
                ->icon('heroicon-o-bell-alert')
                ->modalHeading(__('Send a website notification'))
                ->modalDescription(__('It shows up in the bell on the taskbar.'))
                ->schema([
                    Radio::make('audience')
                        ->label(__('Who gets it'))
                        ->options([
                            'everyone' => __('Everyone'),
                            'online' => __('Players online in the hotel'),
                            'rank' => __('A rank and up'),
                            'player' => __('One player'),
                        ])
                        ->default('everyone')
                        ->live()
                        ->required(),
                    TextInput::make('rank')
                        ->label(__('Minimum rank'))
                        ->numeric()
                        ->minValue(1)
                        ->visible(fn (Get $get): bool => $get('audience') === 'rank')
                        ->required(fn (Get $get): bool => $get('audience') === 'rank'),
                    TextInput::make('username')
                        ->label(__('Username'))
                        ->visible(fn (Get $get): bool => $get('audience') === 'player')
                        ->required(fn (Get $get): bool => $get('audience') === 'player')
                        ->exists(User::class, 'username'),
                    TextInput::make('message')
                        ->label(__('Message'))
                        ->required()
                        ->maxLength(300),
                    TextInput::make('url')
                        ->label(__('Link (optional)'))
                        ->maxLength(300),
                ])
                ->action(function (array $data): void {
                    $sent = static::send($data);

                    Notification::make()->success()->title(trans_choice('Sent to :count player|Sent to :count players', $sent, ['count' => $sent]))->send();
                }),
        ];
    }

    /** @param  array<string, mixed>  $data */
    public static function send(array $data): int
    {
        $ids = match ((string) $data['audience']) {
            'online' => User::query()->where('online', '1')->pluck('id'),
            'rank' => User::query()->where('rank', '>=', (int) ($data['rank'] ?? 1))->pluck('id'),
            'player' => User::query()->where('username', (string) ($data['username'] ?? ''))->pluck('id'),
            default => User::query()->pluck('id'),
        };

        $url = (string) ($data['url'] ?? '');

        return app(Notifier::class)->broadcast($ids, 'announcement', (string) $data['message'], $url === '' ? null : $url, auth()->user());
    }
}

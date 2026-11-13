<?php

namespace App\Filament\Resources\Hotel\Social\Pages;

use App\Filament\Resources\Hotel\Social\SocialReportResource;
use App\Models\Miscellaneous\WebsiteSetting;
use App\Models\User;
use App\Services\SettingsService;
use App\Services\Social\DiscordWebhook;
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
            Action::make('discord')
                ->label(__('Discord webhook'))
                ->icon('heroicon-o-link')
                ->color('gray')
                ->modalHeading(__('Discord webhook'))
                ->modalDescription(__('Shop purchases, gifts and referrals get posted to this Discord channel. Leave it empty to turn it off.'))
                ->fillForm(fn (): array => ['url' => (string) setting('discord_webhook_url', '')])
                ->schema([
                    TextInput::make('url')
                        ->label(__('Webhook URL'))
                        ->placeholder('https://discord.com/api/webhooks/...')
                        ->regex(DiscordWebhook::PATTERN)
                        ->maxLength(300),
                ])
                ->action(function (array $data): void {
                    WebsiteSetting::query()->updateOrCreate(['key' => 'discord_webhook_url'], ['value' => (string) ($data['url'] ?? ''), 'comment' => 'Discord webhook for hotel events']);
                    app(SettingsService::class)->refresh();

                    $sent = filled($data['url'] ?? null) && app(DiscordWebhook::class)->send(__('✅ Webhook connected'), __('Hotel events will show up here.'));

                    Notification::make()->success()->title($sent ? __('Saved. Check Discord for a test message.') : __('Saved'))->send();
                }),
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

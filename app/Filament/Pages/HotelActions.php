<?php

namespace App\Filament\Pages;

use App\Enums\CurrencyTypes;
use App\Exceptions\RconConnectionException;
use App\Filament\Forms\BadgePicker;
use App\Services\Hotel\HotelControl;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

/**
 * Things that happen to the whole hotel at once: alerts, gifts for everyone online, and reloads.
 */
class HotelActions extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-megaphone';

    protected static string|\UnitEnum|null $navigationGroup = 'Hotel';

    protected static ?int $navigationSort = -10;

    protected string $view = 'filament.pages.hotel-actions';

    public static function canAccess(): bool
    {
        return hasHousekeepingPermission('manage_hotel');
    }

    public static function getNavigationLabel(): string
    {
        return __('Hotel actions');
    }

    public function getTitle(): string
    {
        return __('Hotel actions');
    }

    public function onlineCount(): int
    {
        return app(HotelControl::class)->onlineCount();
    }

    public function gameServerOnline(): bool
    {
        return app(HotelControl::class)->isGameServerOnline();
    }

    /** @return array<string, string> */
    public function reloads(): array
    {
        return app(HotelControl::class)->reloads();
    }

    public function alertEveryoneAction(): Action
    {
        return Action::make('alertEveryone')
            ->label(__('Send an alert'))
            ->icon('heroicon-o-megaphone')
            ->modalHeading(__('Alert everyone online'))
            ->modalDescription(__('Pops up for every player in the hotel right now.'))
            ->schema([
                Textarea::make('message')->label(__('Message'))->required()->maxLength(500)->rows(4),
            ])
            ->action(fn (array $data) => $this->report(
                app(HotelControl::class)->alertEveryone(trim($data['message'])),
                __('Alert sent'),
            ));
    }

    public function giveBadgeAction(): Action
    {
        return Action::make('giveBadge')
            ->label(__('Give a badge'))
            ->icon('heroicon-o-trophy')
            ->modalHeading(__('Give everyone online a badge'))
            ->schema([
                BadgePicker::make(),
            ])
            ->requiresConfirmation()
            ->action(fn (array $data) => $this->report(
                app(HotelControl::class)->giveEveryoneBadge($data['badge']),
                __('Badge given'),
            ));
    }

    public function giveCurrencyAction(): Action
    {
        return Action::make('giveCurrency')
            ->label(__('Give currency'))
            ->icon('heroicon-o-banknotes')
            ->modalHeading(__('Give everyone online currency'))
            ->schema([
                Select::make('currency')
                    ->label(__('Currency'))
                    ->options([
                        CurrencyTypes::Credits->name => __('Credits'),
                        CurrencyTypes::Duckets->name => __('Duckets'),
                        CurrencyTypes::Diamonds->name => __('Diamonds'),
                    ])
                    ->required()
                    ->default(CurrencyTypes::Credits->name),
                TextInput::make('amount')->label(__('Amount each'))->integer()->minValue(1)->maxValue(100000)->required(),
            ])
            ->requiresConfirmation()
            ->action(fn (array $data) => $this->report(
                app(HotelControl::class)->giveEveryoneCurrency(constant(CurrencyTypes::class . '::' . $data['currency']), (int) $data['amount']),
                __('Currency given'),
            ));
    }

    public function reloadAction(): Action
    {
        return Action::make('reload')
            ->action(function (array $arguments): void {
                $what = (string) ($arguments['what'] ?? '');
                $label = $this->reloads()[$what] ?? $what;

                try {
                    app(HotelControl::class)->reload($what);
                    Notification::make()->success()->title(__(':what reloaded', ['what' => $label]))->send();
                } catch (RconConnectionException $exception) {
                    Notification::make()->danger()->title(__('Could not reload :what', ['what' => $label]))->body($exception->getMessage())->send();
                }
            });
    }

    /** @param array{sent: int, failed: int} $result */
    private function report(array $result, string $title): void
    {
        if ($result['sent'] === 0 && $result['failed'] === 0) {
            Notification::make()->warning()->title(__('Nobody is online'))->body(__('There was no one to send it to.'))->send();

            return;
        }

        $notification = Notification::make()
            ->title($title)
            ->body(trans_choice('Reached :count player.|Reached :count players.', $result['sent']) . ($result['failed'] > 0 ? ' ' . __(':failed could not be reached.', ['failed' => $result['failed']]) : ''));

        ($result['failed'] > 0 ? $notification->warning() : $notification->success())->send();
    }
}

<?php

namespace App\Filament\Pages;

use App\Enums\CurrencyTypes;
use App\Contracts\Rcon;
use App\Exceptions\RconConnectionException;
use App\Models\User;
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

    private function playerField(): Select
    {
        return Select::make('player')
            ->label(__('Player'))
            ->searchable()
            ->required()
            ->getSearchResultsUsing(fn (string $search): array => User::query()
                ->where('username', 'like', addcslashes($search, '%_\\') . '%')
                ->orderBy('username')
                ->limit(20)
                ->pluck('username', 'id')
                ->all());
    }

    private function playerAction(string $name, string $label, string $icon, array $fields, callable $run, string $done, bool $confirm = false): Action
    {
        $action = Action::make($name)
            ->label($label)
            ->icon($icon)
            ->modalHeading($label)
            ->schema([$this->playerField(), ...$fields])
            ->action(function (array $data) use ($run, $done): void {
                $user = User::query()->find($data['player']);

                if ($user === null) {
                    Notification::make()->danger()->title(__('Player not found'))->send();

                    return;
                }

                try {
                    $run($user, $data, app(Rcon::class));
                    Notification::make()->success()->title($done)->body($user->username)->send();
                } catch (RconConnectionException $exception) {
                    Notification::make()->danger()->title(__('The game server did not answer'))->body($exception->getMessage())->send();
                }
            });

        return $confirm ? $action->requiresConfirmation() : $action;
    }

    public function alertPlayerAction(): Action
    {
        return $this->playerAction('alertPlayer', __('Alert a player'), 'heroicon-o-bell-alert',
            [Textarea::make('message')->label(__('Message'))->required()->maxLength(500)->rows(3)],
            fn (User $user, array $data, Rcon $rcon) => $rcon->alertUser($user, trim($data['message'])), __('Alert sent'));
    }

    public function giveFurniAction(): Action
    {
        return $this->playerAction('giveFurni', __('Give furni'), 'heroicon-o-gift',
            [
                TextInput::make('item')->label(__('Furniture ID'))->integer()->minValue(1)->required(),
                TextInput::make('note')->label(__('Gift note'))->maxLength(120)->default(__('Here is a gift from the team.')),
            ],
            fn (User $user, array $data, Rcon $rcon) => $rcon->sendGift($user, (int) $data['item'], (string) $data['note']), __('Gift sent'));
    }

    public function sendToRoomAction(): Action
    {
        return $this->playerAction('sendToRoom', __('Send to a room'), 'heroicon-o-arrow-right-circle',
            [TextInput::make('room')->label(__('Room ID'))->integer()->minValue(1)->required()],
            fn (User $user, array $data, Rcon $rcon) => $rcon->forwardUser($user, (int) $data['room']), __('Player sent'));
    }

    public function disconnectPlayerAction(): Action
    {
        return $this->playerAction('disconnectPlayer', __('Disconnect a player'), 'heroicon-o-power', [],
            fn (User $user, array $data, Rcon $rcon) => $rcon->disconnectUser($user), __('Player disconnected'), confirm: true)
            ->color('danger');
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

<?php

namespace App\Filament\Widgets;

use App\Emulator\Emulator;
use App\Models\User;
use App\Models\User\WebsiteDiscordAccount;
use App\Services\Hotel\HotelControl;
use Filament\Support\Enums\IconPosition;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

/**
 * What is going on in the hotel right now. Cheap counts only, so it can refresh every few seconds.
 */
class HotelLiveOverview extends BaseWidget
{
    protected static ?int $sort = 0;

    protected ?string $pollingInterval = '15s';

    protected function getStats(): array
    {
        $hotel = app(HotelControl::class);
        $serverOnline = Cache::remember('housekeeping.game-server-online', 15, fn (): bool => $hotel->isGameServerOnline());

        return [
            Stat::make(__('Players online'), number_format($hotel->onlineCount()))
                ->description(__('In the hotel right now'))
                ->descriptionIcon('heroicon-m-signal', IconPosition::Before)
                ->color('success'),

            Stat::make(__('New today'), number_format($this->joinedSince(now()->startOfDay())))
                ->description(__(':count this week', ['count' => number_format($this->joinedSince(now()->subDays(7)))]))
                ->descriptionIcon('heroicon-m-user-plus', IconPosition::Before)
                ->color('primary'),

            Stat::make(__('Discord linked'), number_format(WebsiteDiscordAccount::query()->count()))
                ->description(__('Players who sign in with Discord'))
                ->descriptionIcon('heroicon-m-chat-bubble-left-right', IconPosition::Before)
                ->color('info'),

            Stat::make(__('Game server'), $serverOnline ? __('Online') : __('Offline'))
                ->description($serverOnline ? __('Answering commands') : __('Not answering. Live actions will fail.'))
                ->descriptionIcon($serverOnline ? 'heroicon-m-check-circle' : 'heroicon-m-exclamation-triangle', IconPosition::Before)
                ->color($serverOnline ? 'success' : 'danger'),
        ];
    }

    /**
     * Some emulators keep the sign-up time as a date and some as unix seconds.
     */
    private function joinedSince(Carbon $since): int
    {
        $column = (new User)->qualifyColumn(Emulator::playerSchema()->columns['account_created'] ?? 'account_created');
        $storedAsDate = in_array('account_created', Emulator::playerSchema()->timestamps, true);

        return User::query()->where($column, '>=', $storedAsDate ? $since->toDateTimeString() : $since->getTimestamp())->count();
    }
}

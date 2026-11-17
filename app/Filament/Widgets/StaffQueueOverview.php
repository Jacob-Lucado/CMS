<?php

namespace App\Filament\Widgets;

use App\Models\Help\WebsiteHelpCenterTicket;
use App\Models\Radio\RadioEntry;
use App\Models\Social\SocialReport;
use App\Models\User;
use Filament\Support\Enums\IconPosition;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Cache;

class StaffQueueOverview extends BaseWidget
{
    protected static ?int $sort = 2;

    protected ?string $pollingInterval = '60s';

    protected function getStats(): array
    {
        $queues = Cache::remember('housekeeping.dashboard.queues', 60, fn (): array => [
            'tickets' => WebsiteHelpCenterTicket::query()->where('open', 1)->count(),
            'reports' => SocialReport::query()->whereNull('resolved_at')->count(),
            'radio' => RadioEntry::query()->where('status', RadioEntry::PENDING)->count(),
            'signups' => User::query()->where('account_created', '>=', now()->subDay()->timestamp)->count(),
        ]);

        return [
            Stat::make(__('Open tickets'), $queues['tickets'])
                ->descriptionIcon('heroicon-m-lifebuoy', IconPosition::Before)
                ->description(__('Waiting for a reply'))
                ->color($queues['tickets'] > 0 ? 'warning' : 'success'),
            Stat::make(__('Reported posts'), $queues['reports'])
                ->descriptionIcon('heroicon-m-flag', IconPosition::Before)
                ->description(__('Not handled yet'))
                ->color($queues['reports'] > 0 ? 'danger' : 'success'),
            Stat::make(__('Radio suggestions'), $queues['radio'])
                ->descriptionIcon('heroicon-m-musical-note', IconPosition::Before)
                ->description(__('Waiting for review'))
                ->color('gray'),
            Stat::make(__('New players'), $queues['signups'])
                ->descriptionIcon('heroicon-m-user-plus', IconPosition::Before)
                ->description(__('In the last 24 hours'))
                ->color('success'),
        ];
    }
}

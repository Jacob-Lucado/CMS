<?php

namespace App\Filament\Widgets;

use App\Emulator\Emulator;
use App\Models\RoomStreamPlay;
use Filament\Support\Enums\IconPosition;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class MusicOverview extends BaseWidget
{
    protected static ?int $sort = 3;

    protected ?string $pollingInterval = '60s';

    public static function canView(): bool
    {
        return Emulator::driver() === 'plus' && Schema::hasTable('room_stream_plays');
    }

    protected function getStats(): array
    {
        $music = Cache::remember('housekeeping.dashboard.music', 60, function (): array {
            $week = RoomStreamPlay::query()->where('played_at', '>=', now()->subDays(7));
            $top = (clone $week)->selectRaw('url, COUNT(*) as plays')->groupBy('url')->orderByDesc('plays')->orderBy('url')->first();

            return [
                'today' => RoomStreamPlay::query()->where('played_at', '>=', now()->startOfDay())->count(),
                'week' => (clone $week)->count(),
                'djs' => (clone $week)->distinct()->count('set_by'),
                'top' => $top ? [Str::limit((string) preg_replace('#^https?://(www\.)?#', '', $top->url), 28), (int) $top->plays] : null,
            ];
        });

        return [
            Stat::make(__('Songs today'), $music['today'])
                ->descriptionIcon('heroicon-m-musical-note', IconPosition::Before)
                ->description(__('Started in rooms'))
                ->color('primary'),
            Stat::make(__('Songs this week'), $music['week'])
                ->descriptionIcon('heroicon-m-calendar-days', IconPosition::Before)
                ->description(__('In the last 7 days'))
                ->color('primary'),
            Stat::make(__('DJs this week'), $music['djs'])
                ->descriptionIcon('heroicon-m-user-group', IconPosition::Before)
                ->description(__('Players who started a song'))
                ->color('success'),
            Stat::make(__('Top song this week'), $music['top'][0] ?? '-')
                ->descriptionIcon('heroicon-m-fire', IconPosition::Before)
                ->description($music['top'] ? trans_choice(':count play|:count plays', $music['top'][1]) : __('Nothing played yet'))
                ->color('warning'),
        ];
    }
}

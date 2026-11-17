<?php

namespace App\Filament\Widgets;

use App\Models\User;
use Filament\Widgets\ChartWidget;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Cache;

class SignupsChart extends ChartWidget
{
    protected static ?int $sort = 3;

    protected ?string $pollingInterval = null;

    protected ?string $maxHeight = '260px';

    public function getHeading(): string|Htmlable|null
    {
        return __('New players, last 30 days');
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getData(): array
    {
        $days = Cache::remember('housekeeping.dashboard.signups', 600, function (): array {
            $start = now()->subDays(29)->startOfDay();
            $counts = User::query()
                ->where('account_created', '>=', $start->timestamp)
                ->pluck('account_created')
                ->countBy(fn ($ts) => date('Y-m-d', (int) $ts));

            return collect(range(0, 29))->map(function (int $i) use ($start, $counts): array {
                $day = $start->copy()->addDays($i);

                return ['label' => $day->format('M j'), 'value' => (int) ($counts[$day->format('Y-m-d')] ?? 0)];
            })->all();
        });

        return [
            'datasets' => [['label' => __('Signups'), 'data' => array_column($days, 'value'), 'tension' => 0.3]],
            'labels' => array_column($days, 'label'),
        ];
    }
}

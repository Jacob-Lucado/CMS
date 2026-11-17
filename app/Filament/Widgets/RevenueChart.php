<?php

namespace App\Filament\Widgets;

use App\Models\Shop\WebsitePaypalTransaction;
use App\Models\Shop\WebsiteSquarePayment;
use Filament\Widgets\ChartWidget;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Cache;

class RevenueChart extends ChartWidget
{
    protected static ?int $sort = 4;

    protected ?string $pollingInterval = null;

    protected ?string $maxHeight = '260px';

    public static function canView(): bool
    {
        return hasHousekeepingPermission('manage_shop');
    }

    public function getHeading(): string|Htmlable|null
    {
        return __('Shop revenue, last 30 days');
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getData(): array
    {
        $days = Cache::remember('housekeeping.dashboard.revenue', 600, function (): array {
            $start = now()->subDays(29)->startOfDay();
            $sum = [];
            $add = function ($rows) use (&$sum): void {
                foreach ($rows as $row) {
                    $key = $row->credited_at->format('Y-m-d');
                    $sum[$key] = ($sum[$key] ?? 0) + (int) $row->amount;
                }
            };
            $add(WebsiteSquarePayment::query()->whereNotNull('credited_at')->where('credited_at', '>=', $start)->get(['amount', 'credited_at']));
            $add(WebsitePaypalTransaction::query()->whereNotNull('credited_at')->where('credited_at', '>=', $start)->get(['amount', 'credited_at']));

            return collect(range(0, 29))->map(function (int $i) use ($start, $sum): array {
                $day = $start->copy()->addDays($i);

                return ['label' => $day->format('M j'), 'value' => round(($sum[$day->format('Y-m-d')] ?? 0) / 100, 2)];
            })->all();
        });

        return [
            'datasets' => [['label' => __('Revenue'), 'data' => array_column($days, 'value')]],
            'labels' => array_column($days, 'label'),
        ];
    }
}

<?php

namespace App\Filament\Resources\Hotel\Vouchers\Pages;

use App\Filament\Resources\Hotel\Vouchers\VoucherResource;
use App\Models\Voucher;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ManageRecords;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ManageVouchers extends ManageRecords
{
    protected static string $resource = VoucherResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
            Action::make('batch')
                ->label(__('Make a batch'))
                ->icon('heroicon-o-squares-plus')
                ->modalDescription(__('Creates many one-use codes at once, handy for giveaways.'))
                ->schema([
                    TextInput::make('count')->label(__('How many codes'))->numeric()->minValue(1)->maxValue(500)->default(10)->required(),
                    TextInput::make('prefix')->label(__('Prefix'))->maxLength(12)->helperText(__('Optional, for example SUMMER.')),
                    Select::make('type')->label(__('Reward'))->native(false)->options(['credits' => __('Credits'), 'duckets' => __('Duckets')])->default('credits')->required(),
                    TextInput::make('value')->label(__('Amount each'))->numeric()->minValue(1)->default(100)->required(),
                ])
                ->action(function (array $data): void {
                    $prefix = Str::upper(trim((string) ($data['prefix'] ?? '')));
                    $made = 0;
                    while ($made < (int) $data['count']) {
                        $code = ($prefix !== '' ? $prefix . '-' : '') . Str::upper(Str::random(8));
                        if (Voucher::query()->where('voucher', $code)->exists()) {
                            continue;
                        }
                        Voucher::query()->create(['voucher' => $code, 'type' => $data['type'], 'value' => (int) $data['value'], 'max_uses' => 1, 'enabled' => true]);
                        $made++;
                    }
                    Notification::make()->success()->title(__(':count codes created', ['count' => $made]))->send();
                }),
            Action::make('export')
                ->label(__('Export CSV'))
                ->icon('heroicon-o-arrow-down-tray')
                ->action(fn (): StreamedResponse => response()->streamDownload(function (): void {
                    $out = fopen('php://output', 'w');
                    fputcsv($out, ['code', 'reward', 'amount', 'used', 'max_uses', 'enabled']);
                    Voucher::query()->orderBy('voucher')->each(fn (Voucher $v) => fputcsv($out, [$v->voucher, $v->type, $v->value, $v->current_uses, $v->max_uses, (int) $v->enabled]));
                    fclose($out);
                }, 'vouchers.csv', ['Content-Type' => 'text/csv'])),
        ];
    }
}

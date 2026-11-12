<?php

namespace App\Emulator\Drivers\Ada;

use App\Emulator\Contracts\CurrencyRepository;
use App\Emulator\Support\LeaderboardEntries;
use App\Enums\CurrencyTypes;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class AdaCurrencyRepository implements CurrencyRepository
{
    public function balance(User $user, CurrencyTypes $currency): int
    {
        return (int) (DB::table('player_data')
            ->where('player_id', $user->id)
            ->value($this->column($currency)) ?? 0);
    }

    public function give(User $user, CurrencyTypes $currency, int $amount): void
    {
        if ($amount === 0) {
            return;
        }

        $query = DB::table('player_data')->where('player_id', $user->id);
        $amount > 0
            ? $query->increment($this->column($currency), $amount)
            : $query->decrement($this->column($currency), abs($amount));
    }

    public function deduct(User $user, CurrencyTypes $currency, int $amount): bool
    {
        // A zero decrement changes no rows, which the driver would otherwise
        // report as an unaffordable purchase.
        if ($amount <= 0) {
            return true;
        }

        $column = $this->column($currency);

        return DB::table('player_data')
            ->where('player_id', $user->id)
            ->where($column, '>=', $amount)
            ->decrement($column, $amount) === 1;
    }

    public function topBy(CurrencyTypes $currency, int $limit, array $excludeUserIds = []): Collection
    {
        $column = $this->column($currency);

        $balances = DB::table('player_data')
            ->whereNotIn('player_id', $excludeUserIds)
            ->orderByDesc($column)
            ->limit($limit)
            ->pluck($column, 'player_id')
            ->map(fn ($value): int => (int) $value)
            ->all();

        /** @var array<int, int> $balances */
        return LeaderboardEntries::forUsers($balances);
    }

    private function column(CurrencyTypes $currency): string
    {
        return match ($currency) {
            CurrencyTypes::Credits => 'credit_balance',
            CurrencyTypes::Duckets => 'pixel_balance',
            CurrencyTypes::Diamonds => 'seasonal_balance',
            CurrencyTypes::Points => 'gotw_points',
        };
    }
}

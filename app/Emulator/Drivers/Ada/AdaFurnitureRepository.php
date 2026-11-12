<?php

namespace App\Emulator\Drivers\Ada;

use App\Emulator\Contracts\FurnitureRepository;
use App\Emulator\Data\FurnitureHolding;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Ada inventory rows live in player_furniture_items, referencing the base
 * definition through furniture_item_id.
 */
class AdaFurnitureRepository implements FurnitureRepository
{
    /** Keeps a large grant inside MySQL's placeholder and packet limits. */
    private const INSERT_CHUNK = 500;

    public function definitionCount(): int
    {
        return DB::table('furniture_items')->count();
    }

    public function isLimitedEdition(int $baseItemId): bool
    {
        // player_furniture_items.limited_data carries the serial for limited
        // editions, but Ada's catalog has no limited stock to sell yet, so
        // every purchase is written unlimited.
        return false;
    }

    public function grant(User $user, int $baseItemId, int $amount): void
    {
        if ($amount < 1) {
            return;
        }

        $row = [
            'player_id' => $user->id,
            'furniture_item_id' => $baseItemId,
            'limited_data' => '',
            'meta_data' => '',
            'created_at' => now(),
        ];

        foreach (array_chunk(array_fill(0, $amount, $row), self::INSERT_CHUNK) as $chunk) {
            DB::table('player_furniture_items')->insert($chunk);
        }
    }

    public function holdings(int $baseItemId, int $limit = 100): Collection
    {
        return DB::table('player_furniture_items')
            ->where('furniture_item_id', $baseItemId)
            ->groupBy('player_id')
            ->selectRaw('player_id as user_id, COUNT(*) as item_count')
            ->orderByDesc('item_count')
            ->limit($limit)
            ->get()
            ->map(fn (object $row) => new FurnitureHolding(
                (int) data_get($row, 'user_id'),
                (int) data_get($row, 'item_count'),
            ));
    }
}

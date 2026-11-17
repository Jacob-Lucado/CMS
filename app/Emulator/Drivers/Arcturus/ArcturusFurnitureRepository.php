<?php

namespace App\Emulator\Drivers\Arcturus;

use App\Emulator\Contracts\FurnitureRepository;
use App\Emulator\Data\FurnitureDefinition;
use App\Emulator\Data\FurnitureHolding;
use App\Models\Game\Furniture\Item;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Arcturus inventory rows live in items, referencing items_base via item_id.
 */
class ArcturusFurnitureRepository implements FurnitureRepository
{
    /** Keeps a large grant inside the server's placeholder and packet limits. */
    private const INSERT_CHUNK = 500;

    public function definitionCount(): int
    {
        return DB::table('items_base')->count();
    }

    public function isLimitedEdition(int $baseItemId): bool
    {
        return DB::table('catalog_items')
            ->where('item_ids', (string) $baseItemId)
            ->where('limited_stack', '>', 0)
            ->exists();
    }

    public function grant(User $user, int $baseItemId, int $amount): void
    {
        if ($amount < 1) {
            return;
        }

        // One multi-row insert instead of a query per item; the remaining
        // columns fall back to the table defaults. Chunked so a large-quantity
        // package stays inside the server's placeholder and packet limits.
        $row = [
            'user_id' => $user->id,
            'item_id' => $baseItemId,
        ];

        foreach (array_chunk(array_fill(0, $amount, $row), self::INSERT_CHUNK) as $chunk) {
            Item::query()->insert($chunk);
        }
    }

    public function holdings(int $baseItemId, int $limit = 100): Collection
    {
        return DB::table('items')
            ->where('item_id', $baseItemId)
            ->groupBy('user_id')
            ->selectRaw('user_id, COUNT(*) as item_count')
            ->orderByDesc('item_count')
            ->limit($limit)
            ->get()
            ->map(fn (object $row) => new FurnitureHolding(
                (int) data_get($row, 'user_id'),
                (int) data_get($row, 'item_count'),
            ));
    }

    public function definitions(string $search, int $perPage = 48): LengthAwarePaginator
    {
        $like = '%' . addcslashes($search, '%_\\') . '%';

        return DB::table('items_base')
            ->where('type', 's')
            ->when($search !== '', fn ($query) => $query->where(fn ($query) => $query->where('public_name', 'like', $like)->orWhere('item_name', 'like', $like)))
            ->orderBy('public_name')
            ->paginate($perPage, ['id', 'item_name', 'public_name', 'type', 'width', 'length', 'allow_trade'])
            ->withQueryString()
            ->through(fn (object $row) => new FurnitureDefinition(
                (int) $row->id,
                (string) $row->item_name,
                (string) $row->public_name,
                (string) $row->type,
                (int) $row->width,
                (int) $row->length,
                (bool) $row->allow_trade,
            ));
    }
}

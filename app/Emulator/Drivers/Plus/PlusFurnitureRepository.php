<?php

namespace App\Emulator\Drivers\Plus;

use App\Emulator\Contracts\FurnitureRepository;
use App\Emulator\Data\FurnitureDefinition;
use App\Emulator\Data\FurnitureHolding;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

final class PlusFurnitureRepository implements FurnitureRepository
{
    public function definitionCount(): int
    {
        return DB::table('furniture')->count();
    }

    public function isLimitedEdition(int $baseItemId): bool
    {
        return DB::table('catalog_offer_products')
            ->join('catalog_offer_limited', 'catalog_offer_limited.offer_id', '=', 'catalog_offer_products.offer_id')
            ->where('catalog_offer_products.furniture_id', $baseItemId)->exists();
    }

    public function grant(User $user, int $baseItemId, int $amount): void
    {
        $row = ['user_id' => $user->id, 'room_id' => 0, 'base_item' => $baseItemId, 'extra_data' => ''];
        foreach (array_chunk(array_fill(0, max(0, $amount), $row), 500) as $chunk) {
            DB::table('items')->insert($chunk);
        }
    }

    public function holdings(int $baseItemId, int $limit = 100): Collection
    {
        return DB::table('items')->where('base_item', $baseItemId)->groupBy('user_id')->selectRaw('user_id, COUNT(*) item_count')
            ->orderByDesc('item_count')->limit($limit)->get()
            ->map(fn (object $row) => new FurnitureHolding((int) $row->user_id, (int) $row->item_count));
    }

    public function definitions(string $search, int $perPage = 48): LengthAwarePaginator
    {
        $like = '%' . addcslashes($search, '%_\\') . '%';

        return DB::table('furniture')
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

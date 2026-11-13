<?php

namespace App\Emulator\Drivers\Plus;

use App\Emulator\Contracts\MarketplaceRepository;
use App\Emulator\Data\MarketOffer;
use App\Emulator\Data\MarketTrend;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * PlusEMU keeps offers in catalog_marketplace_offers (state 1 for sale, 2 sold) and sale totals per sprite in
 * catalog_marketplace_data.
 */
final class PlusMarketplaceRepository implements MarketplaceRepository
{
    public function available(): bool
    {
        return Schema::hasTable('catalog_marketplace_offers');
    }

    public function listings(int $limit, ?string $search = null): Collection
    {
        return $this->offers()
            ->where('o.state', '1')
            ->when(filled($search), fn (Builder $query) => $query->where(fn (Builder $inner) => $inner->where('o.public_name', 'like', '%' . $search . '%')->orWhere('f.public_name', 'like', '%' . $search . '%')))
            ->orderBy('o.total_price')
            ->limit($limit)
            ->get()
            ->map($this->offer(...));
    }

    public function recentSales(int $limit): Collection
    {
        return $this->offers()->where('o.state', '2')->orderByDesc('o.offer_id')->limit($limit)->get()->map($this->offer(...));
    }

    public function topMovers(int $limit): Collection
    {
        if (! Schema::hasTable('catalog_marketplace_data')) {
            return collect();
        }

        return DB::table('catalog_marketplace_data as d')
            ->leftJoin('furniture as f', 'f.sprite_id', '=', 'd.sprite')
            ->where('d.sold', '>', 0)
            ->groupBy('d.sprite', 'd.sold', 'd.avgprice')
            ->orderByDesc('d.sold')
            ->limit($limit)
            ->get(['d.sold', 'd.avgprice', DB::raw('MAX(f.public_name) as name'), DB::raw('MAX(f.item_name) as item_name')])
            ->map(fn (object $row): MarketTrend => new MarketTrend((string) ($row->name ?? '?'), (string) ($row->item_name ?? ''), (int) $row->sold, (int) $row->avgprice));
    }

    public function find(int $offerId): ?MarketOffer
    {
        $row = $this->offers()->where('o.offer_id', $offerId)->first();

        return $row === null ? null : $this->offer($row);
    }

    private function offers(): Builder
    {
        return DB::table('catalog_marketplace_offers as o')
            ->leftJoin('furniture as f', 'f.id', '=', 'o.furni_id')
            ->select(['o.offer_id', 'o.public_name', 'o.total_price', 'o.user_id', 'o.listed_at', 'o.state', 'o.limited_number', 'o.limited_stack', 'f.item_name', 'f.public_name as furni_name']);
    }

    private function offer(object $row): MarketOffer
    {
        /** @var object{offer_id: int, public_name: string, total_price: int, user_id: int, listed_at: string|null, state: string, limited_number: int, limited_stack: int, item_name: string|null, furni_name: string|null} $row */
        return new MarketOffer(
            (int) $row->offer_id,
            (string) ($row->furni_name ?: $row->public_name),
            (string) $row->item_name,
            (int) $row->total_price,
            (int) $row->user_id,
            $row->listed_at ? Carbon::parse($row->listed_at) : null,
            (string) $row->state === '2',
            (int) $row->limited_number,
            (int) $row->limited_stack,
        );
    }
}

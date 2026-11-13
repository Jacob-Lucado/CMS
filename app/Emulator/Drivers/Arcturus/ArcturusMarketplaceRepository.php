<?php

namespace App\Emulator\Drivers\Arcturus;

use App\Emulator\Contracts\MarketplaceRepository;
use App\Emulator\Data\MarketOffer;
use App\Emulator\Data\MarketTrend;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Arcturus keeps offers in marketplace_items: state 1 is for sale, 2 is sold.
 */
final class ArcturusMarketplaceRepository implements MarketplaceRepository
{
    public function available(): bool
    {
        return Schema::hasTable('marketplace_items');
    }

    public function listings(int $limit, ?string $search = null): Collection
    {
        return $this->offers()
            ->where('m.state', 1)
            ->when(filled($search), fn (Builder $query) => $query->where('b.public_name', 'like', '%' . $search . '%'))
            ->orderBy('m.price')
            ->limit($limit)
            ->get()
            ->map($this->offer(...));
    }

    public function recentSales(int $limit): Collection
    {
        return $this->offers()->where('m.state', 2)->orderByDesc('m.sold_timestamp')->limit($limit)->get()->map($this->offer(...));
    }

    public function topMovers(int $limit): Collection
    {
        return DB::table('marketplace_items as m')
            ->join('items as i', 'i.id', '=', 'm.item_id')
            ->join('items_base as b', 'b.id', '=', 'i.item_id')
            ->where('m.state', 2)
            ->groupBy('b.id', 'b.public_name', 'b.item_name')
            ->orderByDesc('sold')
            ->limit($limit)
            ->get(['b.public_name', 'b.item_name', DB::raw('COUNT(*) as sold'), DB::raw('AVG(m.price) as average')])
            ->map(fn (object $row): MarketTrend => new MarketTrend((string) $row->public_name, (string) $row->item_name, (int) $row->sold, (int) round((float) $row->average)));
    }

    public function find(int $offerId): ?MarketOffer
    {
        $row = $this->offers()->where('m.id', $offerId)->first();

        return $row === null ? null : $this->offer($row);
    }

    private function offers(): Builder
    {
        return DB::table('marketplace_items as m')
            ->join('items as i', 'i.id', '=', 'm.item_id')
            ->join('items_base as b', 'b.id', '=', 'i.item_id')
            ->select(['m.id', 'm.price', 'm.user_id', 'm.timestamp', 'm.state', 'b.public_name', 'b.item_name']);
    }

    private function offer(object $row): MarketOffer
    {
        /** @var object{id: int, price: int, user_id: int, timestamp: int, state: int, public_name: string, item_name: string} $row */
        return new MarketOffer((int) $row->id, (string) $row->public_name, (string) $row->item_name, (int) $row->price, (int) $row->user_id, $row->timestamp ? Carbon::createFromTimestamp((int) $row->timestamp) : null, (int) $row->state === 2);
    }
}

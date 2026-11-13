<?php

namespace App\Emulator\Contracts;

use App\Emulator\Data\MarketOffer;
use App\Emulator\Data\MarketTrend;
use Illuminate\Support\Collection;

interface MarketplaceRepository
{
    public function available(): bool;

    /**
     * Offers for sale now, cheapest first, optionally filtered by furni name.
     *
     * @return Collection<int, MarketOffer>
     */
    public function listings(int $limit, ?string $search = null): Collection;

    /** @return Collection<int, MarketOffer> */
    public function recentSales(int $limit): Collection;

    /**
     * The furni that sell most.
     *
     * @return Collection<int, MarketTrend>
     */
    public function topMovers(int $limit): Collection;

    public function find(int $offerId): ?MarketOffer;
}

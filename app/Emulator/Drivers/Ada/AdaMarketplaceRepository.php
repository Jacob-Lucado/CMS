<?php

namespace App\Emulator\Drivers\Ada;

use App\Emulator\Contracts\MarketplaceRepository;
use Illuminate\Support\Collection;

/**
 * Ada has no Marketplace the website can read.
 */
final class AdaMarketplaceRepository implements MarketplaceRepository
{
    public function available(): bool
    {
        return false;
    }

    public function listings(int $limit, ?string $search = null): Collection
    {
        return collect();
    }

    public function recentSales(int $limit): Collection
    {
        return collect();
    }

    public function topMovers(int $limit): Collection
    {
        return collect();
    }

    public function find(int $offerId): null
    {
        return null;
    }
}

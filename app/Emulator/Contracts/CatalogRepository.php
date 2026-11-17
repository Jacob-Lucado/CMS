<?php

namespace App\Emulator\Contracts;

use App\Emulator\Data\CatalogOffer;
use App\Emulator\Data\CatalogPage;
use Illuminate\Support\Collection;

/**
 * Reads the catalog the emulator sells from, for the public catalog browser.
 */
interface CatalogRepository
{
    /**
     * Pages a player can see, in catalog order.
     *
     * @return Collection<int, CatalogPage>
     */
    public function pages(): Collection;

    /**
     * What a page sells.
     *
     * @return Collection<int, CatalogOffer>
     */
    public function offers(int $pageId): Collection;
}

<?php

namespace App\Emulator\Drivers\Ada;

use App\Emulator\Contracts\CatalogRepository;
use Illuminate\Support\Collection;

/**
 * Ada's catalog schema is not read yet, so the browser shows no pages.
 */
final class AdaCatalogRepository implements CatalogRepository
{
    public function pages(): Collection
    {
        return collect();
    }

    public function offers(int $pageId): Collection
    {
        return collect();
    }
}

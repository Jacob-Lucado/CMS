<?php

namespace App\Emulator\Drivers\Arcturus;

use App\Emulator\Contracts\CatalogRepository;
use App\Emulator\Data\CatalogOffer;
use App\Emulator\Data\CatalogPage;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

final class ArcturusCatalogRepository implements CatalogRepository
{
    public function pages(): Collection
    {
        return DB::table('catalog_pages')
            ->where('visible', '1')->where('enabled', '1')->where('min_rank', '<=', 1)
            ->orderBy('order_num')->orderBy('caption')
            ->get(['id', 'parent_id', 'caption'])
            ->map(fn (object $row) => new CatalogPage((int) $row->id, (int) $row->parent_id > 0 ? (int) $row->parent_id : null, (string) $row->caption));
    }

    public function offers(int $pageId): Collection
    {
        $rows = DB::table('catalog_items')->where('page_id', $pageId)->orderBy('order_number')->orderBy('id')
            ->get(['id', 'item_ids', 'catalog_name', 'cost_credits', 'cost_points', 'points_type']);

        $bases = DB::table('items_base')
            ->whereIn('id', $rows->map(fn (object $row) => (int) explode(';', (string) $row->item_ids)[0])->filter()->unique())
            ->pluck('item_name', 'id');

        return $rows->map(function (object $row) use ($bases): CatalogOffer {
            $itemName = $bases->get((int) explode(';', (string) $row->item_ids)[0]);

            return new CatalogOffer(
                (int) $row->id,
                (string) $row->catalog_name,
                (int) $row->cost_credits,
                (int) $row->cost_points,
                (int) $row->points_type,
                $itemName ? $itemName . '_icon.png' : null,
            );
        });
    }
}

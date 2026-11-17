<?php

namespace App\Emulator\Drivers\Plus;

use App\Emulator\Contracts\CatalogRepository;
use App\Emulator\Data\CatalogOffer;
use App\Emulator\Data\CatalogPage;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

final class PlusCatalogRepository implements CatalogRepository
{
    public function pages(): Collection
    {
        return DB::table('catalog_pages')
            ->where('visible', 1)->where('enabled', 1)->whereNull('required_permission')
            ->orderBy('position')->orderBy('caption')
            ->get(['id', 'parent_id', 'caption'])
            ->map(fn (object $row) => new CatalogPage((int) $row->id, $row->parent_id === null ? null : (int) $row->parent_id, (string) $row->caption));
    }

    public function offers(int $pageId): Collection
    {
        $rows = DB::table('catalog_page_offers')
            ->join('catalog_offers', 'catalog_offers.id', '=', 'catalog_page_offers.offer_id')
            ->where('catalog_page_offers.page_id', $pageId)->where('catalog_offers.enabled', 1)
            ->orderBy('catalog_page_offers.position')
            ->get(['catalog_offers.id', 'catalog_offers.localization_key', 'catalog_offers.cost_credits', 'catalog_offers.cost_points', 'catalog_offers.points_type']);

        $first = DB::table('catalog_offer_products')
            ->leftJoin('furniture', 'furniture.id', '=', 'catalog_offer_products.furniture_id')
            ->whereIn('catalog_offer_products.offer_id', $rows->pluck('id'))
            ->orderBy('catalog_offer_products.position')
            ->get(['catalog_offer_products.offer_id', 'catalog_offer_products.product_type', 'catalog_offer_products.badge_code', 'furniture.public_name', 'furniture.item_name'])
            ->unique('offer_id')->keyBy('offer_id');

        return $rows->map(function (object $row) use ($first): CatalogOffer {
            $product = $first->get($row->id);
            $name = $product?->public_name ?: ($product?->badge_code ?: $row->localization_key);

            return new CatalogOffer(
                (int) $row->id,
                (string) $name,
                (int) $row->cost_credits,
                (int) $row->cost_points,
                (int) $row->points_type,
                $product?->item_name ? $product->item_name . '_icon.png' : null,
            );
        });
    }
}

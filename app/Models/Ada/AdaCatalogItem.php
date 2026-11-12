<?php

namespace App\Models\Ada;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * A purchasable catalog entry on Ada's schema.
 *
 * @property int $id
 * @property string|null $name
 * @property int $cost_credits
 * @property int $cost_points
 * @property int $cost_points_type
 * @property bool $requires_club_membership
 * @property string|null $meta_data
 * @property int $amount
 * @property int|null $catalog_page_id
 * @property-read AdaCatalogPage|null $page
 * @property-read Collection<int, AdaFurnitureItem> $furniture
 */
class AdaCatalogItem extends Model
{
    protected $table = 'catalog_items';

    protected $guarded = ['id'];

    public $timestamps = false;

    /** @return BelongsTo<AdaCatalogPage, $this> */
    public function page(): BelongsTo
    {
        return $this->belongsTo(AdaCatalogPage::class, 'catalog_page_id');
    }

    /** @return BelongsToMany<AdaFurnitureItem, $this> */
    public function furniture(): BelongsToMany
    {
        return $this->belongsToMany(
            AdaFurnitureItem::class,
            'catalog_item_furniture_item',
            'catalog_items_id',
            'furniture_items_id',
        );
    }
}

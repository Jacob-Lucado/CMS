<?php

namespace App\Models\Ada;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A catalog page on Ada's schema. Pages nest through catalog_page_id.
 *
 * @property int $id
 * @property string|null $name
 * @property string|null $caption
 * @property int|null $catalog_page_id
 * @property int $order_id
 * @property bool $enabled
 * @property bool $visible
 * @property-read Collection<int, AdaCatalogItem> $items
 */
class AdaCatalogPage extends Model
{
    protected $table = 'catalog_pages';

    protected $guarded = ['id'];

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'enabled' => 'boolean',
            'visible' => 'boolean',
            'images_json' => 'json',
            'texts_json' => 'json',
        ];
    }

    /** @return HasMany<AdaCatalogItem, $this> */
    public function items(): HasMany
    {
        return $this->hasMany(AdaCatalogItem::class, 'catalog_page_id');
    }
}

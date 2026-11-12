<?php

namespace App\Models\Ada;

use Illuminate\Database\Eloquent\Model;

/**
 * A base furniture definition; player_furniture_items references it by id.
 *
 * @property int $id
 * @property string $name
 */
class AdaFurnitureItem extends Model
{
    protected $table = 'furniture_items';

    public $timestamps = false;

    protected $guarded = ['id'];
}

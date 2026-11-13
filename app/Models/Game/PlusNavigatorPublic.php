<?php

namespace App\Models\Game;

use Illuminate\Database\Eloquent\Model;

/**
 * A room listed in the Public tab of the PlusEMU navigator.
 *
 * @property int $room_id
 * @property string $caption
 * @property string $description
 * @property string $image_url
 * @property int $order_num
 * @property bool $enabled
 */
class PlusNavigatorPublic extends Model
{
    public $timestamps = false;

    public $incrementing = false;

    protected $table = 'navigator_publics';

    protected $primaryKey = 'room_id';

    protected $fillable = ['room_id', 'caption', 'description', 'image_url', 'order_num', 'enabled'];

    protected $attributes = ['description' => '', 'image_url' => '', 'order_num' => 1, 'enabled' => true];

    protected function casts(): array
    {
        return ['enabled' => 'boolean', 'order_num' => 'integer', 'room_id' => 'integer'];
    }
}

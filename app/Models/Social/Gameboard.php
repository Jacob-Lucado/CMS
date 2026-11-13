<?php

namespace App\Models\Social;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $name
 * @property string|null $description
 * @property int $item_id
 * @property int|null $room_id
 * @property int $order_num
 * @property bool $enabled
 */
class Gameboard extends Model
{
    protected $table = 'website_gameboards';

    protected $fillable = ['name', 'description', 'item_id', 'room_id', 'order_num', 'enabled'];

    protected $attributes = ['order_num' => 0, 'enabled' => true];

    protected function casts(): array
    {
        return ['enabled' => 'boolean', 'item_id' => 'integer', 'room_id' => 'integer', 'order_num' => 'integer'];
    }
}

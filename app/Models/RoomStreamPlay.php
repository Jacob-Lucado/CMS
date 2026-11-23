<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $room_id
 * @property string $set_by
 * @property string $kind
 * @property string $url
 * @property Carbon $played_at
 */
class RoomStreamPlay extends Model
{
    public $timestamps = false;

    protected $table = 'room_stream_plays';

    protected $guarded = [];

    protected function casts(): array
    {
        return ['played_at' => 'datetime'];
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $user_id
 * @property int $strikes
 * @property bool $restricted
 * @property Carbon $updated_at
 */
class RoomStreamStrike extends Model
{
    public $timestamps = false;

    protected $table = 'room_stream_strikes';

    protected $primaryKey = 'user_id';

    public $incrementing = false;

    protected $guarded = [];

    protected function casts(): array
    {
        return ['restricted' => 'boolean', 'updated_at' => 'datetime'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}

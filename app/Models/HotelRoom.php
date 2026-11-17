<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * A room from the emulator's rooms table, for housekeeping.
 *
 * @property int $id
 * @property string $caption
 * @property int $owner
 * @property string $description
 * @property string $state
 * @property int $users_now
 * @property int $users_max
 * @property int $score
 * @property string $tags
 */
class HotelRoom extends Model
{
    protected $table = 'rooms';

    public $timestamps = false;

    protected $guarded = [];

    public function ownerUser(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'owner');
    }
}

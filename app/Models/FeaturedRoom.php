<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $room_id
 * @property string|null $note
 * @property bool $room_of_the_week
 * @property int $position
 */
class FeaturedRoom extends Model
{
    protected $table = 'website_featured_rooms';

    protected $guarded = ['id'];

    protected static function booted(): void
    {
        // Only one room can hold the weekly spot.
        static::saved(function (self $featured): void {
            if ($featured->room_of_the_week) {
                static::query()->where('id', '!=', $featured->id)->update(['room_of_the_week' => false]);
            }
        });
    }

    protected function casts(): array
    {
        return ['room_of_the_week' => 'boolean'];
    }

    /** @return BelongsTo<HotelRoom, $this> */
    public function room(): BelongsTo
    {
        return $this->belongsTo(HotelRoom::class, 'room_id');
    }
}

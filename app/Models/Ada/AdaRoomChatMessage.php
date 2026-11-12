<?php

namespace App\Models\Ada;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * A chat line spoken in a room.
 *
 * @property int $id
 * @property int $room_id
 * @property int $player_id
 * @property string|null $message
 * @property Carbon $created_at
 * @property-read User|null $player
 * @property-read AdaRoom|null $room
 */
class AdaRoomChatMessage extends Model
{
    protected $table = 'room_chat_messages';

    protected $guarded = [];

    public $timestamps = false;

    protected function casts(): array
    {
        return ['created_at' => 'datetime'];
    }

    /** @return BelongsTo<User, $this> */
    public function player(): BelongsTo
    {
        return $this->belongsTo(User::class, 'player_id');
    }

    /** @return BelongsTo<AdaRoom, $this> */
    public function room(): BelongsTo
    {
        return $this->belongsTo(AdaRoom::class, 'room_id');
    }
}

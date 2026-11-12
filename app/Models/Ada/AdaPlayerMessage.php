<?php

namespace App\Models\Ada;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * A private message between two players.
 *
 * @property int $id
 * @property int $origin_player_id
 * @property int $target_player_id
 * @property string|null $message
 * @property Carbon $created_at
 * @property-read User|null $sender
 * @property-read User|null $receiver
 */
class AdaPlayerMessage extends Model
{
    protected $table = 'player_messages';

    protected $guarded = [];

    public $timestamps = false;

    protected function casts(): array
    {
        return ['created_at' => 'datetime'];
    }

    /** @return BelongsTo<User, $this> */
    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'origin_player_id');
    }

    /** @return BelongsTo<User, $this> */
    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'target_player_id');
    }
}

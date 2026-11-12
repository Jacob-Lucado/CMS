<?php

namespace App\Models\Ada;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * An account ban. Ada separates player bans from address bans.
 *
 * @property int $id
 * @property int $creator_id
 * @property int $player_id
 * @property string $reason
 * @property Carbon $created_at
 * @property Carbon|null $expires_at Null means never
 * @property-read User|null $player
 * @property-read User|null $creator
 */
class AdaPlayerBan extends Model
{
    protected $table = 'player_bans';

    protected $guarded = ['id'];

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function player(): BelongsTo
    {
        return $this->belongsTo(User::class, 'player_id');
    }

    /** @return BelongsTo<User, $this> */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creator_id');
    }
}

<?php

namespace App\Models\Ada;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * An address ban. Ada separates address bans from player bans.
 *
 * @property int $id
 * @property int $creator_id
 * @property string $ip_address
 * @property string $reason
 * @property Carbon $created_at
 * @property Carbon|null $expires_at Null means never
 * @property-read User|null $creator
 */
class AdaIpBan extends Model
{
    protected $table = 'banned_ip_addresses';

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
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creator_id');
    }
}

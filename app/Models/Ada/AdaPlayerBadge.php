<?php

namespace App\Models\Ada;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A badge a player owns. slot is 0 when the badge is not equipped.
 *
 * @property int $id
 * @property int $player_id
 * @property int $badge_id
 * @property int $slot
 * @property-read AdaBadge|null $badge
 */
class AdaPlayerBadge extends Model
{
    protected $table = 'player_badges';

    protected $guarded = ['id'];

    public $timestamps = false;

    /** @return BelongsTo<AdaBadge, $this> */
    public function badge(): BelongsTo
    {
        return $this->belongsTo(AdaBadge::class, 'badge_id');
    }
}

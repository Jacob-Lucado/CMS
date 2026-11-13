<?php

namespace App\Models\Social;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $user_id
 * @property Carbon $week
 * @property string $look
 * @property string $gender
 * @property int $votes
 * @property bool $winner
 * @property-read User|null $user
 */
class Outfit extends Model
{
    protected $table = 'website_outfits';

    protected $fillable = ['user_id', 'week', 'look', 'gender', 'votes', 'winner'];

    protected $attributes = ['votes' => 0, 'winner' => false];

    protected function casts(): array
    {
        return ['week' => 'date', 'winner' => 'boolean', 'votes' => 'integer'];
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

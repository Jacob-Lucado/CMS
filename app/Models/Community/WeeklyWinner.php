<?php

namespace App\Models\Community;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property Carbon $week_start
 * @property string $board
 * @property int $user_id
 * @property int $score
 * @property string|null $badge
 */
class WeeklyWinner extends Model
{
    protected $table = 'website_weekly_winners';

    protected $fillable = ['week_start', 'board', 'user_id', 'score', 'badge'];

    protected function casts(): array
    {
        return ['week_start' => 'date', 'score' => 'integer'];
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

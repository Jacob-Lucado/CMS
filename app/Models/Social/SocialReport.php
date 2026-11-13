<?php

namespace App\Models\Social;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $post_id
 * @property int $user_id
 * @property string $reason
 * @property int|null $resolved_by
 * @property Carbon|null $resolved_at
 * @property-read SocialPost|null $post
 * @property-read User|null $user
 */
class SocialReport extends Model
{
    protected $table = 'website_social_reports';

    protected $fillable = ['post_id', 'user_id', 'reason', 'resolved_by', 'resolved_at'];

    protected function casts(): array
    {
        return ['resolved_at' => 'datetime'];
    }

    /** @return BelongsTo<SocialPost, $this> */
    public function post(): BelongsTo
    {
        return $this->belongsTo(SocialPost::class)->withTrashed();
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

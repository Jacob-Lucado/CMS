<?php

namespace App\Models\Social;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $target_type
 * @property string $target_id
 * @property int $user_id
 * @property string $body
 * @property Carbon $created_at
 * @property-read User|null $user
 */
class SocialComment extends Model
{
    protected $table = 'website_social_comments';

    protected $fillable = ['target_type', 'target_id', 'user_id', 'body'];

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

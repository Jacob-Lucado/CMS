<?php

namespace App\Models\Social;

use App\Models\User;
use Database\Factories\Social\SocialPostFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $user_id
 * @property string $type
 * @property string|null $body
 * @property array<string, mixed>|null $data
 * @property int|null $shared_post_id
 * @property Carbon $created_at
 * @property-read User|null $user
 * @property-read SocialPost|null $shared
 */
class SocialPost extends Model
{
    /** @use HasFactory<SocialPostFactory> */
    use HasFactory, SoftDeletes;

    public const STATUS = 'status';

    public const PHOTO = 'photo';

    public const VIDEO = 'video';

    public const OUTFIT = 'outfit';

    public const PROFILE = 'profile';

    public const SHARE = 'share';

    public const REFERRAL = 'referral';

    public const LISTING = 'listing';

    protected $table = 'website_social_posts';

    protected $fillable = ['user_id', 'type', 'body', 'data', 'shared_post_id'];

    protected function casts(): array
    {
        return ['data' => 'array'];
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return BelongsTo<SocialPost, $this> */
    public function shared(): BelongsTo
    {
        return $this->belongsTo(self::class, 'shared_post_id');
    }

    /** @return BelongsToMany<User, $this> */
    public function tagged(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'website_social_tags', 'post_id', 'user_id');
    }

    /** @return HasMany<SocialReport, $this> */
    public function reports(): HasMany
    {
        return $this->hasMany(SocialReport::class, 'post_id');
    }
}

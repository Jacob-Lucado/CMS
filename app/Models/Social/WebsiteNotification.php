<?php

namespace App\Models\Social;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $user_id
 * @property string $type
 * @property int|null $actor_id
 * @property string $message
 * @property string|null $url
 * @property Carbon|null $read_at
 * @property Carbon $created_at
 * @property-read User|null $actor
 */
class WebsiteNotification extends Model
{
    protected $table = 'website_notifications';

    protected $fillable = ['user_id', 'type', 'actor_id', 'message', 'url', 'read_at'];

    protected function casts(): array
    {
        return ['read_at' => 'datetime'];
    }

    /** @return BelongsTo<User, $this> */
    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }
}

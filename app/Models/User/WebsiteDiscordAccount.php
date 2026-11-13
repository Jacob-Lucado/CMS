<?php

namespace App\Models\User;

use App\Models\User;
use Database\Factories\User\WebsiteDiscordAccountFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $user_id
 * @property string $discord_id
 * @property string $discord_username
 * @property string|null $discord_avatar
 */
class WebsiteDiscordAccount extends Model
{
    /** @use HasFactory<WebsiteDiscordAccountFactory> */
    use HasFactory;

    protected $fillable = ['user_id', 'discord_id', 'discord_username', 'discord_avatar'];

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function avatarUrl(): ?string
    {
        if ($this->discord_avatar === null) {
            return null;
        }

        return sprintf('https://cdn.discordapp.com/avatars/%s/%s.png?size=128', $this->discord_id, $this->discord_avatar);
    }
}

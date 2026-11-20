<?php

namespace App\Models\Radio;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int|null $user_id
 * @property string $message
 */
class RadioShoutout extends Model
{
    /** A shout-out stays on the radio for this many minutes. */
    public const VISIBLE_MINUTES = 60;

    protected $table = 'website_radio_shoutouts';

    protected $guarded = [];

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return array{id: int, message: string, by: string, mine: bool} */
    public function toPlayer(User $viewer): array
    {
        return [
            'id' => $this->id,
            'message' => $this->message,
            'by' => $this->user?->username ?? __('Someone'),
            'mine' => $this->user_id === $viewer->id,
        ];
    }
}

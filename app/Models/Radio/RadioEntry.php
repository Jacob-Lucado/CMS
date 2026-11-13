<?php

namespace App\Models\Radio;

use App\Models\User;
use Database\Factories\Radio\RadioEntryFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property string $kind
 * @property string $status
 * @property string $title
 * @property string $subtitle
 * @property string $url
 * @property string $stream
 * @property string|null $art
 * @property int|null $user_id
 * @property int|null $reviewed_by
 */
class RadioEntry extends Model
{
    /** @use HasFactory<RadioEntryFactory> */
    use HasFactory;

    public const TRACK = 'track';

    public const STATION = 'station';

    public const APPROVED = 'approved';

    public const PENDING = 'pending';

    public const REJECTED = 'rejected';

    protected $table = 'website_radio_entries';

    protected $fillable = ['kind', 'status', 'title', 'subtitle', 'url', 'stream', 'art', 'user_id', 'reviewed_by'];

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return BelongsTo<User, $this> */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    /**
     * @param  Builder<self>  $query
     *
     * @return Builder<self>
     */
    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('status', self::APPROVED);
    }

    /**
     * @param  Builder<self>  $query
     *
     * @return Builder<self>
     */
    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', self::PENDING);
    }

    /** A playlist on SoundCloud plays through by itself. */
    public function isSet(): bool
    {
        return $this->kind === self::TRACK && str_contains($this->stream, '/playlists/');
    }

    /** @return array<string, mixed> */
    public function toPlayer(?User $viewer = null): array
    {
        return [
            'id' => $this->id,
            'kind' => $this->kind,
            'status' => $this->status,
            'title' => $this->title,
            'subtitle' => $this->subtitle,
            'url' => $this->url,
            'stream' => $this->stream,
            'art' => $this->art,
            'set' => $this->isSet(),
            'by' => $this->user?->username,
            'mine' => $viewer !== null && $this->user_id === $viewer->id,
        ];
    }
}

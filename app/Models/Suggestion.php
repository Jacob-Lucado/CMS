<?php

namespace App\Models;

use App\Models\Social\WebsiteNotification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property int $user_id
 * @property string $title
 * @property string $body
 * @property string $status open, planned, done or declined.
 * @property string|null $staff_reply
 */
class Suggestion extends Model
{
    public const STATUSES = ['open', 'planned', 'done', 'declined'];

    protected $table = 'website_suggestions';

    protected $guarded = ['id'];

    protected static function booted(): void
    {
        // Tell the author when staff move their idea along.
        static::updated(function (self $suggestion): void {
            if ($suggestion->wasChanged('status') && $suggestion->status !== 'open') {
                WebsiteNotification::query()->create([
                    'user_id' => $suggestion->user_id,
                    'type' => 'suggestion',
                    'message' => __('Your idea ":title" is now :status.', ['title' => $suggestion->title, 'status' => $suggestion->status]),
                    'url' => route('suggestions.index', absolute: false),
                ]);
            }
        });
    }

    /** @return BelongsTo<User, $this> */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /** @return HasMany<SuggestionVote, $this> */
    public function votes(): HasMany
    {
        return $this->hasMany(SuggestionVote::class, 'suggestion_id');
    }
}

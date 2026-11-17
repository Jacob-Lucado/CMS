<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $question
 * @property string $options One answer per line.
 * @property bool $published
 * @property \Illuminate\Support\Carbon|null $closes_at
 */
class WebsitePoll extends Model
{
    protected $table = 'website_polls';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['published' => 'boolean', 'closes_at' => 'datetime'];
    }

    /** @return HasMany<WebsitePollVote, $this> */
    public function votes(): HasMany
    {
        return $this->hasMany(WebsitePollVote::class, 'poll_id');
    }

    /** @return list<string> */
    public function choices(): array
    {
        return array_values(array_filter(array_map('trim', preg_split('/\R/', $this->options) ?: [])));
    }

    public function isOpen(): bool
    {
        return $this->published && ($this->closes_at === null || $this->closes_at->isFuture());
    }
}

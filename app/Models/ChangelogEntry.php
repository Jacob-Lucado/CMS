<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $title
 * @property string $category
 * @property string $body
 * @property bool $published
 * @property \Illuminate\Support\Carbon|null $published_at
 */
class ChangelogEntry extends Model
{
    protected $table = 'website_changelog_entries';

    protected $guarded = ['id'];

    protected static function booted(): void
    {
        static::saving(function (self $entry): void {
            if ($entry->published && $entry->published_at === null) {
                $entry->published_at = now();
            }
        });
    }

    protected function casts(): array
    {
        return ['published' => 'boolean', 'published_at' => 'datetime'];
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $title
 * @property string|null $description
 * @property string|null $location
 * @property \Illuminate\Support\Carbon $starts_at
 * @property bool $published
 */
class WebsiteEvent extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['starts_at' => 'datetime', 'published' => 'boolean'];
    }
}

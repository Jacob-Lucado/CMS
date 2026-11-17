<?php

namespace App\Models;

use App\Services\Social\DiscordWebhook;
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

    protected static function booted(): void
    {
        static::created(function (self $event): void {
            if (! $event->published) {
                return;
            }

            app(DiscordWebhook::class)->send(
                __('New event: :title', ['title' => $event->title]),
                trim($event->starts_at->format('l, F j \a\t g:i A T') . ($event->location ? "\n" . $event->location : '') . ($event->description ? "\n\n" . $event->description : '')),
            );
        });
    }

    protected function casts(): array
    {
        return ['starts_at' => 'datetime', 'published' => 'boolean'];
    }
}

<?php

namespace App\Console\Commands;

use App\Models\Social\WebsiteNotification;
use App\Models\WebsiteEvent;
use App\Services\Social\DiscordWebhook;
use Illuminate\Console\Command;

class RemindEventAttendees extends Command
{
    protected $signature = 'events:remind';

    protected $description = 'Remind signed-up players about events that start within the hour';

    public function handle(DiscordWebhook $discord): int
    {
        $events = WebsiteEvent::query()
            ->where('published', true)
            ->whereNull('reminded_at')
            ->whereBetween('starts_at', [now(), now()->addHour()])
            ->withCount('attendees')
            ->get();

        foreach ($events as $event) {
            $event->attendees()->pluck('users.id')->each(function (int $userId) use ($event): void {
                WebsiteNotification::query()->create([
                    'user_id' => $userId,
                    'type' => 'event_reminder',
                    'message' => __(':title starts at :time.', ['title' => $event->title, 'time' => $event->starts_at->format('g:i A T')]),
                    'url' => route('events.index', absolute: false),
                ]);
            });

            $discord->send(
                __('Starting soon: :title', ['title' => $event->title]),
                trim($event->starts_at->format('g:i A T') . ($event->location ? "\n" . $event->location : '') . "\n" . trans_choice(':count player is going|:count players are going', $event->attendees_count)),
            );

            $event->forceFill(['reminded_at' => now()])->save();
        }

        $this->info(trans_choice('Reminded about :count event.|Reminded about :count events.', $events->count()));

        return self::SUCCESS;
    }
}

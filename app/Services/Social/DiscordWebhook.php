<?php

namespace App\Services\Social;

use Illuminate\Support\Facades\Http;
use Throwable;

/**
 * Posts hotel events to the Discord channel set in Housekeeping.
 */
class DiscordWebhook
{
    public const PATTERN = '~^https://(?:canary\.|ptb\.)?discord(?:app)?\.com/api/webhooks/\d+/[\w-]+$~';

    public function send(string $title, string $description, int $color = 0x4DA3FF): bool
    {
        $url = (string) setting('discord_webhook_url', '');

        if (preg_match(self::PATTERN, $url) !== 1) {
            return false;
        }

        try {
            return Http::timeout(4)->post($url, [
                'username' => (string) setting('hotel_name', config('app.name')),
                'embeds' => [[
                    'title' => mb_substr($title, 0, 250),
                    'description' => mb_substr($description, 0, 2000),
                    'color' => $color,
                    'timestamp' => now()->toIso8601String(),
                ]],
            ])->successful();
        } catch (Throwable) {
            return false;
        }
    }
}

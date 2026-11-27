<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $ban_key
 * @property string $url
 * @property string $reason
 * @property string $banned_by
 * @property Carbon $created_at
 */
class RoomStreamBan extends Model
{
    public $timestamps = false;

    protected $table = 'room_stream_bans';

    protected $guarded = [];

    protected function casts(): array
    {
        return ['created_at' => 'datetime'];
    }

    /** The key the emulator bans a link by: the playlist when there is one, otherwise the video or track. */
    public static function keyFor(string $link): ?string
    {
        $parts = parse_url(trim($link));

        if (! $parts || ($parts['scheme'] ?? '') !== 'https' || empty($parts['host'])) {
            return null;
        }

        $host = strtolower($parts['host']);
        $path = strtolower(rtrim($parts['path'] ?? '', '/'));
        parse_str($parts['query'] ?? '', $query);

        if (in_array($host, ['youtube.com', 'www.youtube.com', 'm.youtube.com', 'music.youtube.com', 'youtu.be'], true)) {
            if (! empty($query['list']) && preg_match('/^[A-Za-z0-9_-]{10,64}$/', $query['list'])) {
                return 'youtube-list:' . $query['list'];
            }

            $video = $query['v'] ?? null;

            if ($video === null && preg_match('#^/(?:shorts/|embed/|live/)?([A-Za-z0-9_-]{11})$#i', $parts['path'] ?? '', $match)) {
                $video = $match[1];
            }

            return $video !== null && preg_match('/^[A-Za-z0-9_-]{11}$/', $video) ? 'youtube:' . $video : null;
        }

        if (in_array($host, ['soundcloud.com', 'www.soundcloud.com', 'm.soundcloud.com', 'on.soundcloud.com', 'api.soundcloud.com'], true)) {
            return 'soundcloud:' . str_replace(['www.', 'm.'], '', $host) . $path;
        }

        return 'stream:' . $host . $path;
    }
}

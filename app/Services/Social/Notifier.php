<?php

namespace App\Services\Social;

use App\Models\Miscellaneous\WebsitePermission;
use App\Models\Social\SocialProfile;
use App\Models\Social\WebsiteNotification;
use App\Models\User;
use Illuminate\Support\Collection;

/**
 * Sends website notifications, skipping the kinds a player has turned off.
 */
class Notifier
{
    /**
     * Every kind of notification, grouped the way the settings page shows them.
     *
     * @var array<string, array<string, string>>
     */
    public const TYPES = [
        'Social activity' => [
            'post_like' => 'Likes on your posts',
            'post_comment' => 'Comments on your posts',
            'photo_like' => 'Likes on your photos',
            'photo_comment' => 'Comments on your photos',
            'post_share' => 'Shares of your posts and photos',
            'kudos' => 'Kudos received',
            'tag' => 'Being tagged',
            'outfit_like' => 'Votes on your outfit',
        ],
        'Profile activity' => [
            'guestbook' => 'Guestbook comments',
            'profile_rating' => 'Profile ratings',
        ],
        'Shop' => [
            'gift_received' => 'Gifts received',
            'gift_sent' => 'Gifts sent',
            'purchase' => 'Purchases',
            'top_up' => 'Credit top-ups',
        ],
        'Tickets' => [
            'ticket_reply' => 'Replies to your tickets',
            'ticket_created' => 'New tickets (staff)',
            'ticket_staff_reply' => 'Replies on tickets (staff)',
        ],
        'News' => [
            'article' => 'New articles',
        ],
        'Badges' => [
            'badge_approved' => 'Your badge was approved',
            'badge_submitted' => 'Badges waiting for review (staff)',
        ],
        'Reports' => [
            'post_reported' => 'Reported posts (staff)',
            'post_removed' => 'Your post was removed by staff',
            'announcement' => 'Hotel announcements',
        ],
    ];

    /** @return list<string> */
    public static function types(): array
    {
        return array_merge(...array_map(array_keys(...), array_values(self::TYPES)));
    }

    public function send(User|int $to, string $type, string $message, ?string $url = null, ?User $actor = null): ?WebsiteNotification
    {
        $userId = $to instanceof User ? $to->id : $to;

        if ($actor instanceof User && $actor->id === $userId) {
            return null;
        }

        if (in_array($type, $this->muted($userId), true)) {
            return null;
        }

        return WebsiteNotification::query()->create([
            'user_id' => $userId,
            'type' => $type,
            'actor_id' => $actor?->id,
            'message' => mb_substr($message, 0, 300),
            'url' => $url === null ? null : mb_substr($url, 0, 300),
        ]);
    }

    /**
     * Sends one notification to many players in a single insert.
     *
     * @param  iterable<int>  $userIds
     */
    public function broadcast(iterable $userIds, string $type, string $message, ?string $url = null, ?User $actor = null): int
    {
        $muted = SocialProfile::query()->whereNotNull('muted')->pluck('muted', 'user_id');
        $now = now();
        $sent = 0;

        foreach (collect($userIds)->unique()->chunk(500) as $chunk) {
            $rows = $chunk
                ->reject(fn (int $id): bool => $id === $actor?->id || in_array($type, (array) ($muted[$id] ?? []), true))
                ->map(fn (int $id): array => [
                    'user_id' => $id,
                    'type' => $type,
                    'actor_id' => $actor?->id,
                    'message' => mb_substr($message, 0, 300),
                    'url' => $url,
                    'created_at' => $now,
                    'updated_at' => $now,
                ])->values()->all();

            WebsiteNotification::query()->insert($rows);
            $sent += count($rows);
        }

        return $sent;
    }

    /**
     * Tells everyone who holds a website permission, such as the staff who review reports.
     */
    public function staff(string $permission, string $type, string $message, ?string $url = null, ?User $actor = null): int
    {
        return $this->broadcast($this->holders($permission), $type, $message, $url, $actor);
    }

    /** @return Collection<int, int> */
    public function holders(string $permission): Collection
    {
        $minRank = WebsitePermission::query()->where('permission', $permission)->value('min_rank');

        if ($minRank === null) {
            return collect();
        }

        return User::query()->where('rank', '>=', (int) $minRank)->limit(200)->pluck('id');
    }

    /** @return list<string> */
    public function muted(int $userId): array
    {
        return array_values((array) (SocialProfile::query()->find($userId)->muted ?? []));
    }
}

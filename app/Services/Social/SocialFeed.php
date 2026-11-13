<?php

namespace App\Services\Social;

use App\Emulator\Data\Photo;
use App\Models\Social\SocialComment;
use App\Models\Social\SocialLike;
use App\Models\Social\SocialPost;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;

/**
 * Turns posts, photos and comments into the shape the feed page reads.
 */
class SocialFeed
{
    public const POST = 'post';

    public const PHOTO = 'photo';

    /** @return array{id: int, name: string, head: string, figure: string, home: string}|null */
    public function author(?User $user): ?array
    {
        if (! $user instanceof User) {
            return null;
        }

        $imager = (string) setting('avatar_imager');

        return [
            'id' => $user->id,
            'name' => $user->username,
            'head' => $imager . $user->look . '&direction=2&head_direction=2&headonly=1&gesture=sml&size=m',
            'figure' => $imager . $user->look . '&direction=2&head_direction=3&gesture=sml&size=l',
            'home' => route('home.show', $user->username, false),
        ];
    }

    /**
     * @param  EloquentCollection<int, SocialPost>  $posts
     *
     * @return array<int, array<string, mixed>>
     */
    public function posts(EloquentCollection $posts, User $viewer, bool $staff): array
    {
        $posts->loadMissing(['user', 'tagged', 'shared.user']);
        $ids = array_map(intval(...), $posts->modelKeys());
        [$likes, $liked, $comments] = $this->counts(self::POST, $ids, $viewer);
        $shares = SocialPost::query()->whereIn('shared_post_id', $ids)->selectRaw('shared_post_id, count(*) as total')->groupBy('shared_post_id')->pluck('total', 'shared_post_id');

        return $posts->map(fn (SocialPost $post): array => [
            ...$this->post($post),
            'likes' => (int) ($likes[$post->id] ?? 0),
            'liked' => isset($liked[$post->id]),
            'comments' => (int) ($comments[$post->id] ?? 0),
            'shares' => (int) ($shares[$post->id] ?? 0),
            'mine' => $post->user_id === $viewer->id,
            'can_delete' => $staff || $post->user_id === $viewer->id,
            'shared' => $post->shared ? $this->post($post->shared) : null,
        ])->values()->all();
    }

    /** @return array<string, mixed> */
    public function post(SocialPost $post): array
    {
        return [
            'id' => $post->id,
            'type' => $post->type,
            'body' => (string) $post->body,
            'data' => $post->data ?? [],
            'author' => $this->author($post->user),
            'tagged' => $post->relationLoaded('tagged') ? $post->tagged->map(fn (User $user): array => ['name' => $user->username, 'home' => route('home.show', $user->username, false)])->values()->all() : [],
            'at' => $post->created_at->toIso8601String(),
            'ago' => $post->created_at->diffForHumans(),
        ];
    }

    /**
     * @param  Collection<int, Photo>  $photos
     *
     * @return array<int, array<string, mixed>>
     */
    public function photos(Collection $photos, User $viewer): array
    {
        $ids = $photos->map(fn (Photo $photo): string => $photo->id)->values()->all();
        $users = User::query()->whereIn('id', $photos->map(fn (Photo $photo): int => $photo->userId)->unique()->values()->all())->get()->keyBy('id');
        [$likes, $liked, $comments] = $this->counts(self::PHOTO, $ids, $viewer);

        return $photos->map(fn (Photo $photo): array => [
            'id' => $photo->id,
            'url' => $photo->url,
            'author' => $this->author($users->get($photo->userId)),
            'ago' => $photo->takenAt?->diffForHumans(),
            'likes' => (int) ($likes[$photo->id] ?? 0),
            'liked' => isset($liked[$photo->id]),
            'comments' => (int) ($comments[$photo->id] ?? 0),
        ])->values()->all();
    }

    /** @return array<string, mixed> */
    public function comment(SocialComment $comment, User $viewer, bool $staff): array
    {
        return [
            'id' => $comment->id,
            'body' => $comment->body,
            'author' => $this->author($comment->user),
            'ago' => $comment->created_at->diffForHumans(),
            'can_delete' => $staff || $comment->user_id === $viewer->id,
        ];
    }

    /**
     * Who is tagged: the picked names plus any @name written in the text.
     *
     * @param  list<string>  $picked
     *
     * @return Collection<int, User>
     */
    public function mentions(string $body, array $picked = []): Collection
    {
        preg_match_all('/(?<![\w@])@([A-Za-z0-9_\-.=:,!?]{1,25})/u', $body, $matches);
        $names = collect([...$picked, ...$matches[1]])->map(fn (string $name): string => trim($name, '.,!?:'))->filter()->unique()->take(20);

        return $names->isEmpty() ? collect() : User::query()->whereIn('username', $names->all())->get();
    }

    /** Reads the video id from any YouTube link, or a bare id. */
    public static function youtubeId(string $link): ?string
    {
        $link = trim($link);

        if (preg_match('/^[A-Za-z0-9_-]{11}$/', $link) === 1) {
            return $link;
        }

        $pattern = '~^(?:https?://)?(?:www\.|m\.|music\.)?(?:youtube\.com/(?:watch\?(?:.*&)?v=|embed/|shorts/|live/|v/)|youtu\.be/)([A-Za-z0-9_-]{11})~i';

        return preg_match($pattern, $link, $match) === 1 ? $match[1] : null;
    }

    /**
     * @param  array<int, int|string>  $ids
     *
     * @return array{0: Collection<array-key, mixed>, 1: array<array-key, bool>, 2: Collection<array-key, mixed>}
     */
    private function counts(string $type, array $ids, User $viewer): array
    {
        if ($ids === []) {
            return [collect(), [], collect()];
        }

        $likes = SocialLike::query()->where('target_type', $type)->whereIn('target_id', $ids)->selectRaw('target_id, count(*) as total')->groupBy('target_id')->pluck('total', 'target_id');
        $liked = SocialLike::query()->where('target_type', $type)->whereIn('target_id', $ids)->where('user_id', $viewer->id)->pluck('target_id')->flip()->map(fn (): bool => true)->all();
        $comments = SocialComment::query()->where('target_type', $type)->whereIn('target_id', $ids)->selectRaw('target_id, count(*) as total')->groupBy('target_id')->pluck('total', 'target_id');

        return [$likes, $liked, $comments];
    }
}

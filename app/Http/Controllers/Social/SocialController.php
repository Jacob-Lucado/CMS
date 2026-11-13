<?php

namespace App\Http\Controllers\Social;

use App\Emulator\Contracts\PhotoRepository;
use App\Emulator\Data\Photo;
use App\Http\Controllers\Controller;
use App\Models\Social\Outfit;
use App\Models\Social\SocialComment;
use App\Models\Social\SocialLike;
use App\Models\Social\SocialPost;
use App\Models\Social\SocialProfile;
use App\Models\Social\SocialReport;
use App\Models\User;
use App\Services\PermissionsService;
use App\Services\Social\Notifier;
use App\Services\Social\SocialFeed;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class SocialController extends Controller
{
    private const PAGE = 15;

    public function __construct(private readonly SocialFeed $feed, private readonly Notifier $notifier, private readonly PermissionsService $permissions, private readonly PhotoRepository $cameraPhotos) {}

    public function show(Request $request, ?User $user = null): View
    {
        $viewer = $this->viewer($request);
        $owner = $user ?? $viewer;
        $profile = SocialProfile::query()->find($owner->id);

        return view('social', [
            'profileUser' => $user,
            'config' => [
                'feed' => route('social.feed', absolute: false),
                'post' => route('social.posts.store', absolute: false),
                'base' => rtrim(route('social.index', absolute: false), '/'),
                'photos' => route('social.photos', absolute: false),
                'users' => route('social.users', absolute: false),
                'profile' => route('social.profile', absolute: false),
                'notifications' => route('notifications.index', absolute: false),
                'outfits' => route('outfits.index', absolute: false),
                'me' => $this->feed->author($viewer),
                'owner' => $user ? [...($this->feed->author($user) ?? []), 'background' => $profile?->background, 'motto' => (string) $user->motto] : null,
                'background' => $viewer->id === $owner->id ? $profile?->background : null,
                'staff' => $this->isStaff($viewer),
                'imager' => (string) setting('avatar_imager'),
                'camera' => $this->cameraPhotos->available(),
            ],
        ]);
    }

    public function feed(Request $request): JsonResponse
    {
        $viewer = $this->viewer($request);
        $filters = $request->validate([
            'before' => ['nullable', 'integer'],
            'user' => ['nullable', 'integer'],
            'post' => ['nullable', 'integer'],
        ]);

        $posts = SocialPost::query()
            ->when($filters['before'] ?? null, fn ($query, $before) => $query->where('id', '<', $before))
            ->when($filters['user'] ?? null, fn ($query, $userId) => $query->where(fn ($inner) => $inner->where('user_id', $userId)->orWhereHas('tagged', fn ($tag) => $tag->where('users.id', $userId))))
            ->when($filters['post'] ?? null, fn ($query, $postId) => $query->whereKey($postId))
            ->latest('id')
            ->limit(self::PAGE)
            ->get();

        return response()->json([
            'posts' => $this->feed->posts($posts, $viewer, $this->isStaff($viewer)),
            'more' => $posts->count() === self::PAGE,
        ])->header('Cache-Control', 'no-store');
    }

    public function store(Request $request): JsonResponse
    {
        $viewer = $this->viewer($request);
        $data = $request->validate([
            'type' => ['required', Rule::in([SocialPost::STATUS, SocialPost::PHOTO, SocialPost::VIDEO, SocialPost::OUTFIT, SocialPost::PROFILE, SocialPost::SHARE])],
            'body' => ['nullable', 'string', 'max:1000'],
            'photos' => ['array', 'max:6'],
            'photos.*' => ['regex:/^[0-9a-f-]{1,36}$/i'],
            'video' => ['nullable', 'string', 'max:200'],
            'outfit_id' => ['nullable', 'integer'],
            'avatar' => ['nullable', 'string', 'max:200', 'regex:/^[a-z_]+=[A-Za-z0-9_.,-]*(&[a-z_]+=[A-Za-z0-9_.,-]*)*$/'],
            'shared_post_id' => ['nullable', 'integer'],
            'tags' => ['array', 'max:20'],
            'tags.*' => ['string', 'max:25'],
        ]);

        $body = trim((string) ($data['body'] ?? ''));
        $extra = [];
        $sharedId = null;

        switch ($data['type']) {
            case SocialPost::STATUS:
                if ($body === '') {
                    throw ValidationException::withMessages(['body' => __('Write something first.')]);
                }
                break;
            case SocialPost::PHOTO:
                $photos = $this->cameraPhotos->findMany(array_values(array_map(strval(...), (array) ($data['photos'] ?? []))));
                if ($photos->isEmpty()) {
                    throw ValidationException::withMessages(['photos' => __('Pick at least one photo.')]);
                }
                $extra['photos'] = $photos->map(fn (Photo $photo): array => ['id' => $photo->id, 'url' => $photo->url, 'user_id' => $photo->userId])->values()->all();
                break;
            case SocialPost::VIDEO:
                $video = SocialFeed::youtubeId((string) ($data['video'] ?? ''));
                if ($video === null) {
                    throw ValidationException::withMessages(['video' => __("That doesn't look like a YouTube link.")]);
                }
                $extra['video'] = $video;
                break;
            case SocialPost::OUTFIT:
                $entry = isset($data['outfit_id']) ? Outfit::query()->where('user_id', $viewer->id)->whereKey((int) $data['outfit_id'])->first() : null;
                $extra['look'] = $entry->look ?? (string) $viewer->look;
                $extra['gender'] = $entry->gender ?? (string) $viewer->gender;
                if ($entry !== null) {
                    $extra['outfit_id'] = $entry->id;
                }
                if (filled($data['avatar'] ?? null)) {
                    $extra['avatar'] = $this->avatarQuery((string) $data['avatar']);
                }
                break;
            case SocialPost::PROFILE:
                $extra['home'] = route('home.show', $viewer->username, false);
                $extra['look'] = (string) $viewer->look;
                $extra['motto'] = (string) $viewer->motto;
                break;
            case SocialPost::SHARE:
                $original = SocialPost::query()->findOrFail((int) ($data['shared_post_id'] ?? 0));
                $sharedId = $original->shared_post_id ?? $original->id;
                break;
        }

        $post = SocialPost::query()->create([
            'user_id' => $viewer->id,
            'type' => $data['type'],
            'body' => $body === '' ? null : $body,
            'data' => $extra === [] ? null : $extra,
            'shared_post_id' => $sharedId,
        ]);

        $this->afterPosting($post, $viewer, array_values(array_map(strval(...), (array) ($data['tags'] ?? []))));

        return response()->json(['post' => $this->feed->posts(SocialPost::query()->whereKey($post->id)->get(), $viewer, $this->isStaff($viewer))[0]], 201);
    }

    public function destroy(Request $request, SocialPost $post): JsonResponse
    {
        $viewer = $this->viewer($request);
        $staff = $this->isStaff($viewer);
        abort_unless($staff || $post->user_id === $viewer->id, 403);

        if ($post->user_id !== $viewer->id) {
            $this->notifier->send($post->user_id, 'post_removed', __('A staff member removed one of your posts.'), null, $viewer);
        }

        SocialReport::query()->where('post_id', $post->id)->whereNull('resolved_at')->update(['resolved_by' => $viewer->id, 'resolved_at' => now()]);
        $post->delete();

        return response()->json(['ok' => true]);
    }

    public function like(Request $request): JsonResponse
    {
        $viewer = $this->viewer($request);
        [$type, $id, $ownerId, $url] = $this->target($request);
        $key = ['target_type' => $type, 'target_id' => $id, 'user_id' => $viewer->id];

        $existing = SocialLike::query()->where($key);
        if ($existing->exists()) {
            $existing->delete();
            $liked = false;
        } else {
            SocialLike::query()->create($key);
            $liked = true;
            $this->notifier->send($ownerId, $this->likeType($type, $id), __(':name liked your :thing.', ['name' => $viewer->username, 'thing' => $type === SocialFeed::PHOTO ? __('photo') : __('post')]), $url, $viewer);
        }

        return response()->json([
            'liked' => $liked,
            'likes' => SocialLike::query()->where('target_type', $type)->where('target_id', $id)->count(),
        ]);
    }

    public function comments(Request $request): JsonResponse
    {
        $viewer = $this->viewer($request);
        [$type, $id] = $this->target($request);
        $staff = $this->isStaff($viewer);

        $comments = SocialComment::query()->with('user')->where('target_type', $type)->where('target_id', $id)->oldest('id')->limit(200)->get();

        return response()->json(['comments' => $comments->map(fn (SocialComment $comment): array => $this->feed->comment($comment, $viewer, $staff))->values()]);
    }

    public function comment(Request $request): JsonResponse
    {
        $viewer = $this->viewer($request);
        [$type, $id, $ownerId, $url] = $this->target($request);
        $body = trim((string) $request->validate(['body' => ['required', 'string', 'max:500']])['body']);

        $comment = SocialComment::query()->create(['target_type' => $type, 'target_id' => $id, 'user_id' => $viewer->id, 'body' => $body]);

        $this->notifier->send($ownerId, $type === SocialFeed::PHOTO ? 'photo_comment' : 'post_comment', __(':name commented: ":text"', ['name' => $viewer->username, 'text' => mb_strimwidth($body, 0, 80, '...')]), $url, $viewer);

        foreach ($this->feed->mentions($body) as $tagged) {
            if ($tagged->id !== $ownerId) {
                $this->notifier->send($tagged, 'tag', __(':name mentioned you in a comment.', ['name' => $viewer->username]), $url, $viewer);
            }
        }

        return response()->json(['comment' => $this->feed->comment($comment->load('user'), $viewer, $this->isStaff($viewer))], 201);
    }

    public function destroyComment(Request $request, SocialComment $comment): JsonResponse
    {
        $viewer = $this->viewer($request);
        abort_unless($this->isStaff($viewer) || $comment->user_id === $viewer->id, 403);
        $comment->delete();

        return response()->json(['ok' => true]);
    }

    public function report(Request $request, SocialPost $post): JsonResponse
    {
        $viewer = $this->viewer($request);
        $reason = $request->validate(['reason' => ['required', 'string', 'max:300']])['reason'];

        $report = SocialReport::query()->firstOrCreate(['post_id' => $post->id, 'user_id' => $viewer->id], ['reason' => $reason]);

        if ($report->wasRecentlyCreated) {
            $this->notifier->staff('min_staff_rank', 'post_reported', __(':name reported a post by :author.', ['name' => $viewer->username, 'author' => $post->user->username ?? '?']), $this->postUrl($post->id), $viewer);
        }

        return response()->json(['message' => __('Thanks. Staff will take a look.')]);
    }

    public function photos(Request $request): JsonResponse
    {
        $viewer = $this->viewer($request);
        $filters = $request->validate(['offset' => ['nullable', 'integer', 'min:0'], 'mine' => ['nullable', 'boolean'], 'id' => ['nullable', 'regex:/^[0-9a-f-]{1,36}$/i']]);
        $photos = $this->cameraPhotos->latest((int) ($filters['offset'] ?? 0), 12, ($filters['mine'] ?? false) ? $viewer->id : null);
        $more = $photos->count() === 12;

        // A shared photo link opens the viewer on that photo first.
        if (isset($filters['id']) && ($first = $this->cameraPhotos->find((string) $filters['id'])) !== null) {
            $photos = $photos->reject(fn (Photo $photo): bool => $photo->id === $first->id)->prepend($first)->values();
        }

        return response()->json(['photos' => $this->feed->photos($photos, $viewer), 'more' => $more])->header('Cache-Control', 'no-store');
    }

    public function users(Request $request): JsonResponse
    {
        $term = (string) $request->validate(['q' => ['required', 'string', 'min:1', 'max:25']])['q'];

        $users = User::query()->where('username', 'like', str_replace(['%', '_'], ['\%', '\_'], $term) . '%')->orderBy('username')->limit(8)->get(['id', 'username', 'look']);

        return response()->json(['users' => $users->map(fn (User $user): ?array => $this->feed->author($user))->values()]);
    }

    public function updateProfile(Request $request): JsonResponse
    {
        $viewer = $this->viewer($request);
        $data = $request->validate([
            'background' => ['nullable', 'string', 'max:300', 'regex:~^(https://\S+\.(png|jpe?g|gif|webp)(\?\S*)?|/\S+)$~i'],
            'muted' => ['array'],
            'muted.*' => ['string', Rule::in(Notifier::types())],
            'hide_from_rare_directory' => ['boolean'],
        ]);

        $profile = SocialProfile::query()->firstOrNew(['user_id' => $viewer->id]);
        $profile->fill(array_intersect_key($data, $request->all()))->save();

        return response()->json(['ok' => true, 'background' => $profile->background]);
    }

    /** @param  list<string>  $picked */
    private function afterPosting(SocialPost $post, User $viewer, array $picked): void
    {
        $url = $this->postUrl($post->id);
        $tagged = $this->feed->mentions((string) $post->body, $picked)->reject(fn (User $user): bool => $user->id === $viewer->id);
        $post->tagged()->sync($tagged->pluck('id')->all());

        foreach ($tagged as $user) {
            $this->notifier->send($user, 'tag', __(':name tagged you in a post.', ['name' => $viewer->username]), $url, $viewer);
        }

        if ($post->type === SocialPost::SHARE && $post->shared) {
            $this->notifier->send($post->shared->user_id, 'post_share', __(':name shared your post.', ['name' => $viewer->username]), $url, $viewer);
        }

        if ($post->type === SocialPost::PHOTO) {
            foreach (array_unique(array_column((array) ($post->data['photos'] ?? []), 'user_id')) as $ownerId) {
                $this->notifier->send($ownerId, 'post_share', __(':name shared your photo.', ['name' => $viewer->username]), $url, $viewer);
            }
        }
    }

    /** @return array{0: string, 1: string, 2: int, 3: string} */
    private function target(Request $request): array
    {
        $data = $request->validate([
            'target_type' => ['required', Rule::in([SocialFeed::POST, SocialFeed::PHOTO])],
            'target_id' => ['required', 'regex:/^[0-9a-f-]{1,36}$/i'],
        ]);
        $id = (string) $data['target_id'];

        if ($data['target_type'] === SocialFeed::PHOTO) {
            $photo = $this->cameraPhotos->find($id);
            abort_if($photo === null, 404);

            return [SocialFeed::PHOTO, $photo->id, $photo->userId, route('social.index', ['photo' => $photo->id], false)];
        }

        abort_unless(ctype_digit($id), 404);
        $post = SocialPost::query()->findOrFail((int) $id);

        return [SocialFeed::POST, $id, $post->user_id, $this->postUrl($post->id)];
    }

    private function likeType(string $type, string $id): string
    {
        if ($type === SocialFeed::PHOTO) {
            return 'photo_like';
        }

        return SocialPost::query()->whereKey((int) $id)->value('type') === SocialPost::OUTFIT ? 'outfit_like' : 'post_like';
    }

    /** Keeps only the imager options players can change, so a post can't carry another figure. */
    private function avatarQuery(string $query): string
    {
        parse_str($query, $options);
        $allowed = ['direction', 'head_direction', 'action', 'gesture', 'size', 'headonly', 'img_format'];

        return http_build_query(array_intersect_key($options, array_flip($allowed)));
    }

    private function postUrl(int $id): string
    {
        return route('social.index', ['post' => $id], false);
    }

    private function isStaff(User $user): bool
    {
        return $this->permissions->allows($user, 'min_staff_rank');
    }

    private function viewer(Request $request): User
    {
        $user = $request->user();
        abort_unless($user instanceof User, 401);

        return $user;
    }
}

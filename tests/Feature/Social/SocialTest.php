<?php

use App\Filament\Resources\Hotel\Social\Pages\ManageSocialReports;
use App\Models\Home\UserHomeMessage;
use App\Models\Miscellaneous\WebsitePermission;
use App\Models\Social\SocialPost;
use App\Models\Social\SocialProfile;
use App\Models\Social\WebsiteNotification;
use App\Models\User;
use App\Services\PermissionsService;
use App\Services\Social\SocialFeed;

beforeEach(function () {
    installHotel();
    setSetting('force_staff_2fa', '0');
    WebsitePermission::query()->updateOrCreate(['permission' => 'min_staff_rank'], ['min_rank' => 5, 'description' => '']);
    app(PermissionsService::class)->refresh();
});

function poster(User $user): mixed
{
    return test()->flushSession()->actingAs($user);
}

it('posts a status, tags mentioned players and tells them', function () {
    $author = User::factory()->create(['username' => 'Alice']);
    $friend = User::factory()->create(['username' => 'Bob']);

    poster($author)->postJson(route('social.posts.store'), ['type' => 'status', 'body' => 'Party in my room, @Bob!'])
        ->assertCreated()
        ->assertJsonPath('post.tagged.0.name', 'Bob');

    expect(SocialPost::query()->first()->tagged->pluck('id')->all())->toBe([$friend->id])
        ->and(WebsiteNotification::query()->where('user_id', $friend->id)->value('type'))->toBe('tag');
});

it('refuses an empty status and a bad YouTube link', function () {
    $author = User::factory()->create();

    poster($author)->postJson(route('social.posts.store'), ['type' => 'status', 'body' => '  '])->assertUnprocessable();
    poster($author)->postJson(route('social.posts.store'), ['type' => 'video', 'video' => 'https://example.com/watch'])->assertUnprocessable();
    poster($author)->postJson(route('social.posts.store'), ['type' => 'video', 'video' => 'https://youtu.be/dQw4w9WgXcQ'])
        ->assertCreated()
        ->assertJsonPath('post.data.video', 'dQw4w9WgXcQ');
});

it('reads video ids from the common YouTube links', function (string $link) {
    expect(SocialFeed::youtubeId($link))->toBe('dQw4w9WgXcQ');
})->with([
    'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
    'https://youtube.com/watch?feature=share&v=dQw4w9WgXcQ',
    'https://youtube.com/shorts/dQw4w9WgXcQ',
    'dQw4w9WgXcQ',
]);

it('toggles likes and tells the author once', function () {
    $post = SocialPost::factory()->create();
    $fan = User::factory()->create();

    poster($fan)->postJson(route('social.like'), ['target_type' => 'post', 'target_id' => $post->id])->assertJson(['liked' => true, 'likes' => 1]);
    poster($fan)->postJson(route('social.like'), ['target_type' => 'post', 'target_id' => $post->id])->assertJson(['liked' => false, 'likes' => 0]);

    expect(WebsiteNotification::query()->where('user_id', $post->user_id)->where('type', 'post_like')->count())->toBe(1);
});

it('skips notifications a player muted', function () {
    $post = SocialPost::factory()->create();
    SocialProfile::query()->create(['user_id' => $post->user_id, 'muted' => ['post_comment']]);

    poster(User::factory()->create())->postJson(route('social.comments.store'), ['target_type' => 'post', 'target_id' => $post->id, 'body' => 'Nice!'])->assertCreated();

    expect(WebsiteNotification::query()->where('user_id', $post->user_id)->count())->toBe(0);
});

it('shares a post and points at the original', function () {
    $original = SocialPost::factory()->create();
    $sharer = User::factory()->create();

    poster($sharer)->postJson(route('social.posts.store'), ['type' => 'share', 'shared_post_id' => $original->id])
        ->assertCreated()
        ->assertJsonPath('post.shared.id', $original->id);

    expect(WebsiteNotification::query()->where('user_id', $original->user_id)->value('type'))->toBe('post_share');
});

it('lets authors and staff delete posts, but nobody else', function () {
    $post = SocialPost::factory()->create();
    $staff = User::factory()->create(['rank' => 5]);

    poster(User::factory()->create(['rank' => 1]))->deleteJson(route('social.posts.destroy', $post))->assertForbidden();
    poster($staff)->deleteJson(route('social.posts.destroy', $post))->assertOk();

    expect(SocialPost::query()->count())->toBe(0)
        ->and(WebsiteNotification::query()->where('user_id', $post->user_id)->value('type'))->toBe('post_removed');
});

it('sends reports to staff once per player', function () {
    $post = SocialPost::factory()->create();
    $staff = User::factory()->create(['rank' => 6]);
    $reporter = User::factory()->create(['rank' => 1]);

    poster($reporter)->postJson(route('social.posts.report', $post), ['reason' => 'Spam'])->assertOk();
    poster($reporter)->postJson(route('social.posts.report', $post), ['reason' => 'Spam'])->assertOk();

    expect(WebsiteNotification::query()->where('user_id', $staff->id)->where('type', 'post_reported')->count())->toBe(1);
});

it('gives kudos once a day per board', function () {
    $giver = User::factory()->create();
    $winner = User::factory()->create();

    poster($giver)->postJson(route('kudos.store'), ['user_id' => $winner->id, 'board' => 'top_credits'])->assertOk()->assertJson(['kudos' => 1]);
    poster($giver)->postJson(route('kudos.store'), ['user_id' => $winner->id, 'board' => 'top_credits'])->assertUnprocessable();
    poster($giver)->postJson(route('kudos.store'), ['user_id' => $giver->id, 'board' => 'top_credits'])->assertUnprocessable();
});

it('lists and reads notifications, and counts unread ones in the balance', function () {
    $user = User::factory()->create();
    WebsiteNotification::query()->create(['user_id' => $user->id, 'type' => 'announcement', 'message' => 'Hello']);

    poster($user)->getJson(route('me.balance'))->assertJsonPath('unread', 1);
    poster($user)->getJson(route('notifications.feed'))->assertJsonPath('notifications.0.message', 'Hello');
    poster($user)->postJson(route('notifications.read'))->assertOk();
    poster($user)->getJson(route('me.balance'))->assertJsonPath('unread', 0);
});

it('notifies a home owner about guestbook messages', function () {
    $owner = User::factory()->create();
    $visitor = User::factory()->create();

    UserHomeMessage::query()->create(['user_id' => $visitor->id, 'recipient_user_id' => $owner->id, 'content' => 'Hi!']);

    expect(WebsiteNotification::query()->where('user_id', $owner->id)->value('type'))->toBe('guestbook');
});

it('sends Housekeeping announcements to a rank and up', function () {
    User::factory()->create(['rank' => 1]);
    $staff = User::factory()->create(['rank' => 6]);

    expect(ManageSocialReports::send(['audience' => 'rank', 'rank' => 5, 'message' => 'Staff meeting!']))->toBe(1)
        ->and(WebsiteNotification::query()->value('user_id'))->toBe($staff->id);
});

it('renders the feed and notification pages', function () {
    $user = User::factory()->create();
    SocialPost::factory()->create(['user_id' => $user->id, 'body' => 'First!']);

    poster($user)->get(route('social.index'))->assertOk()->assertSee('socialFeed');
    poster($user)->get(route('social.user', $user->username))->assertOk();
    poster($user)->get(route('notifications.index'))->assertOk()->assertSee('hotelNotifications');
    poster($user)->getJson(route('social.feed'))->assertJsonPath('posts.0.body', 'First!');
});

<?php

use App\Emulator\Contracts\PhotoRepository;
use App\Models\Social\SocialPost;
use App\Models\Social\WebsiteNotification;
use App\Models\User;
use Illuminate\Support\Facades\DB;

beforeEach(function () {
    DB::table('roles')->insert(['id' => 1, 'slug' => 'default', 'name' => 'User', 'description' => '', 'weight' => 0, 'security_level' => 1, 'badge_code' => '', 'is_staff' => false, 'is_hidden' => false, 'created_at' => now(), 'updated_at' => now()]);
});

function cameraPhoto(User $user, string $id, bool $published = true): void
{
    DB::table('camera_media')->insert(['id' => $id, 'user_id' => $user->id, 'room_id' => 1, 'created_at' => now()]);

    if ($published) {
        DB::table('camera_publications')->insert(['media_id' => $id, 'user_id' => $user->id, 'room_id' => 1, 'created_at' => now()]);
    }
}

test('the feed lists published photos and a player sees all of their own', function () {
    $photographer = User::factory()->create();
    cameraPhoto($photographer, '11111111-1111-4111-8111-111111111111');
    cameraPhoto($photographer, '22222222-2222-4222-8222-222222222222', published: false);

    $photos = app(PhotoRepository::class);

    expect($photos->latest(0, 12)->pluck('id')->all())->toBe(['11111111-1111-4111-8111-111111111111'])
        ->and($photos->latest(0, 12, $photographer->id))->toHaveCount(2)
        ->and($photos->find('11111111-1111-4111-8111-111111111111')?->url)->toBe('/camera/11111111-1111-4111-8111-111111111111.png');
});

test('players share, like and comment on PlusEMU photos', function () {
    $photographer = User::factory()->create();
    $fan = User::factory()->create();
    $id = '33333333-3333-4333-8333-333333333333';
    cameraPhoto($photographer, $id);

    $this->actingAs($fan)->postJson(route('social.posts.store'), ['type' => 'photo', 'photos' => [$id]])
        ->assertCreated()
        ->assertJsonPath('post.data.photos.0.url', "/camera/{$id}.png");
    $this->actingAs($fan)->postJson(route('social.like'), ['target_type' => 'photo', 'target_id' => $id])->assertJson(['liked' => true, 'likes' => 1]);
    $this->actingAs($fan)->postJson(route('social.comments.store'), ['target_type' => 'photo', 'target_id' => $id, 'body' => 'Great shot'])->assertCreated();
    $this->actingAs($fan)->getJson(route('social.photos', ['id' => $id]))->assertJsonPath('photos.0.likes', 1)->assertJsonPath('photos.0.comments', 1);

    expect(SocialPost::query()->count())->toBe(1)
        ->and(WebsiteNotification::query()->where('user_id', $photographer->id)->pluck('type')->sort()->values()->all())->toBe(['photo_comment', 'photo_like', 'post_share']);
});

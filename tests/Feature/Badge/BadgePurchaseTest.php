<?php

use App\Actions\Badge\CreateDrawnBadge;
use App\Exceptions\BadgePurchaseException;
use App\Models\User;
use App\Models\WebsiteDrawBadge;
use App\Services\Badge\BadgeImageNormalizer;
use Illuminate\Support\Facades\File;

function drawnBadgePayload(): array
{
    $image = imagecreatetruecolor(40, 40);

    ob_start();
    imagegif($image);
    $bytes = ob_get_clean();
    imagedestroy($image);

    return [
        'badge_data' => 'data:image/gif;base64,' . base64_encode($bytes),
        'badge_name' => 'My Badge',
        'badge_description' => 'A hand drawn badge',
    ];
}

function prepareBadgeDirectory(): string
{
    $directory = storage_path('framework/testing/badges');

    File::ensureDirectoryExists($directory);
    File::cleanDirectory($directory);

    setSetting('badge_path_filesystem', $directory);
    setSetting('drawbadge_currency_value', '150');
    setSetting('drawbadge_currency_type', 'credits');

    return $directory;
}

test('buying a drawn badge charges credits and stores the file', function () {
    installHotel();
    $directory = prepareBadgeDirectory();

    $user = User::factory()->create(['credits' => 1000]);

    $response = $this->actingAs($user)
        ->post(route('badge.buy'), drawnBadgePayload())
        ->assertOk()
        ->assertJson(['success' => true]);

    $badge = WebsiteDrawBadge::where('user_id', $user->id)->first();

    expect($badge)->not->toBeNull()
        ->and(file_exists($badge->badge_path))->toBeTrue()
        ->and($response->json('badge_url'))->toBe($badge->badge_url)
        ->and($response->json('badge_path_filesystem'))->toBeNull()
        ->and((int) $user->refresh()->credits)->toBe(850);
});

test('a buyer without enough credits is rejected and no file is left behind', function () {
    installHotel();
    $directory = prepareBadgeDirectory();

    $user = User::factory()->create(['credits' => 100]);

    $this->actingAs($user)
        ->post(route('badge.buy'), drawnBadgePayload())
        ->assertStatus(400)
        ->assertJson(['success' => false]);

    expect(WebsiteDrawBadge::count())->toBe(0)
        ->and(File::files($directory))->toBeEmpty()
        ->and((int) $user->refresh()->credits)->toBe(100);
});

test('invalid image data is rejected by validation', function () {
    installHotel();
    prepareBadgeDirectory();

    $user = User::factory()->create(['credits' => 1000]);

    $this->actingAs($user)
        ->post(route('badge.buy'), [
            'badge_data' => 'data:image/gif;base64,' . base64_encode('not a gif'),
            'badge_name' => 'My Badge',
            'badge_description' => 'A hand drawn badge',
        ])
        ->assertSessionHasErrors('badge_data');

    expect(WebsiteDrawBadge::count())->toBe(0)
        ->and((int) $user->refresh()->credits)->toBe(1000);
});

test('invalid image bytes cannot leave a partial badge or charge', function () {
    installHotel();
    $directory = prepareBadgeDirectory();
    $user = User::factory()->create(['credits' => 1000]);

    expect(fn () => app(CreateDrawnBadge::class)->execute($user, [
        'badge_data' => base64_encode('not a gif'),
        'badge_name' => 'Invalid',
        'badge_description' => 'Invalid',
    ]))->toThrow(BadgePurchaseException::class)
        ->and(File::files($directory))->toBeEmpty()
        ->and(WebsiteDrawBadge::count())->toBe(0)
        ->and((int) $user->refresh()->credits)->toBe(1000);
});

/** A 40x40 GIF with the given number of identical frames, each lasting $delay hundredths of a second. */
function animatedDrawnGif(int $frames, int $delay = 10): string
{
    $image = imagecreatetruecolor(40, 40);
    imagefill($image, 0, 0, imagecolorallocate($image, 200, 30, 30));

    ob_start();
    imagegif($image);
    $bytes = ob_get_clean();
    imagedestroy($image);

    $position = 13 + (ord($bytes[10]) & 0x80 ? 3 * (2 ** ((ord($bytes[10]) & 0x07) + 1)) : 0);
    $header = substr($bytes, 0, $position);
    $frame = substr($bytes, strpos($bytes, ',', $position), -1);
    $loop = "!\xFF\x0BNETSCAPE2.0\x03\x01\x00\x00\x00";
    $pause = "!\xF9\x04\x00" . pack('v', $delay) . "\x00\x00";

    return $header . $loop . str_repeat($pause . $frame, $frames) . ';';
}

function animatedPayload(int $frames, int $delay = 10): array
{
    return ['badge_data' => 'data:image/gif;base64,' . base64_encode(animatedDrawnGif($frames, $delay))] + drawnBadgePayload();
}

test('an animated badge keeps every frame and its timing', function () {
    installHotel();
    prepareBadgeDirectory();
    $user = User::factory()->create(['credits' => 1000]);

    $this->actingAs($user)->post(route('badge.buy'), ['badge_data' => 'data:image/gif;base64,' . base64_encode(animatedDrawnGif(4, 8))] + drawnBadgePayload())
        ->assertOk()
        ->assertJson(['success' => true]);

    $stored = file_get_contents(WebsiteDrawBadge::where('user_id', $user->id)->firstOrFail()->badge_path);

    expect(substr_count($stored, "!\xF9\x04"))->toBe(4)
        ->and($stored)->toContain('NETSCAPE2.0')
        ->and(app(BadgeImageNormalizer::class)->frameDelays($stored))->toBe([8, 8, 8, 8])
        ->and(getimagesizefromstring($stored)[0])->toBe(40);
});

test('an animated badge is limited in frames, speed and size', function (string $gif) {
    installHotel();
    prepareBadgeDirectory();
    $user = User::factory()->create(['credits' => 1000]);

    $this->actingAs($user)->postJson(route('badge.buy'), ['badge_data' => 'data:image/gif;base64,' . base64_encode($gif)] + drawnBadgePayload())
        ->assertUnprocessable()
        ->assertJsonValidationErrors('badge_data');

    expect(WebsiteDrawBadge::count())->toBe(0)->and((int) $user->refresh()->credits)->toBe(1000);
})->with([
    'too many frames' => fn () => animatedDrawnGif(25),
    'flickering too fast' => fn () => animatedDrawnGif(3, 2),
    'far too large' => fn () => substr(animatedDrawnGif(2), 0, -1) . str_repeat("!\xFE\xFF" . str_repeat('a', 255) . "\x00", 500) . ';',
]);

test('the largest allowed animation still saves', function () {
    installHotel();
    prepareBadgeDirectory();
    $user = User::factory()->create(['credits' => 1000]);

    $this->actingAs($user)->post(route('badge.buy'), ['badge_data' => 'data:image/gif;base64,' . base64_encode(animatedDrawnGif(24, 5))] + drawnBadgePayload())
        ->assertOk();
});

test('an animated badge loses comments and trailing data when stored', function () {
    installHotel();
    prepareBadgeDirectory();
    $user = User::factory()->create(['credits' => 1000]);
    $gif = substr(animatedDrawnGif(2), 0, -1) . "!\xFE\x04evil\x00;<?php echo 1;";

    $this->actingAs($user)->post(route('badge.buy'), ['badge_data' => 'data:image/gif;base64,' . base64_encode($gif)] + drawnBadgePayload())->assertOk();

    $stored = file_get_contents(WebsiteDrawBadge::where('user_id', $user->id)->firstOrFail()->badge_path);

    expect($stored)->not->toContain('evil')->not->toContain('<?php')->toEndWith(';');
});

test('the purchase action refuses an animation that skipped validation', function () {
    installHotel();
    prepareBadgeDirectory();
    $user = User::factory()->create(['credits' => 1000]);
    $data = ['badge_data' => 'data:image/gif;base64,' . base64_encode(animatedDrawnGif(30))] + drawnBadgePayload();

    expect(fn () => app(CreateDrawnBadge::class)->execute($user, $data))->toThrow(BadgePurchaseException::class)
        ->and(WebsiteDrawBadge::count())->toBe(0)
        ->and((int) $user->refresh()->credits)->toBe(1000);
});

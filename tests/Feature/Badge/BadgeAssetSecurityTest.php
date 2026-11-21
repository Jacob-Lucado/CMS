<?php

use App\Services\Badge\BadgeImageNormalizer;
use App\Services\Badge\BadgeImageStorage;
use App\Services\Badge\RemoteBadgeImageFetcher;
use App\Services\Network\PublicIpResolver;
use App\Support\BadgeCode;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

function badgeImageBytes(string $format = 'png', int $width = 40, int $height = 40): string
{
    $image = imagecreatetruecolor($width, $height);
    ob_start();

    match ($format) {
        'gif' => imagegif($image),
        'jpeg' => imagejpeg($image),
        default => imagepng($image),
    };

    $bytes = ob_get_clean();
    imagedestroy($image);

    return (string) $bytes;
}

test('badge codes cannot traverse the badge directory', function () {
    expect(fn () => BadgeCode::filename('../outside'))->toThrow(InvalidArgumentException::class)
        ->and(fn () => BadgeCode::filename('SAFE_CODE-1'))->not->toThrow(InvalidArgumentException::class)
        ->and(fn () => BadgeCode::filename('100000_' . str_repeat('A', 26)))->not->toThrow(InvalidArgumentException::class)
        ->and(fn () => BadgeCode::filename(str_repeat('A', 65)))->toThrow(InvalidArgumentException::class);
});

test('remote badge images reject non-https addresses before sending', function () {
    Http::fake();
    $fetcher = app(RemoteBadgeImageFetcher::class);

    expect(fn () => $fetcher->fetch('http://127.0.0.1/internal.gif'))
        ->toThrow(RuntimeException::class);

    Http::assertNothingSent();
});

test('remote badge images reject private addresses before sending', function () {
    Http::fake();

    expect(fn () => app(RemoteBadgeImageFetcher::class)->fetch('https://127.0.0.1/internal.gif'))
        ->toThrow(RuntimeException::class);

    Http::assertNothingSent();
});

test('the public IP resolver rejects private addresses', function () {
    expect(fn () => app(PublicIpResolver::class)->resolveIpv4('127.0.0.1'))
        ->toThrow(RuntimeException::class);
});

test('remote badge images are fetched once and normalized to a 40x40 gif', function () {
    $resolver = Mockery::mock(PublicIpResolver::class);
    $resolver->shouldReceive('resolveIpv4')->once()->with('assets.example.com')->andReturn('93.184.216.34');
    Http::fake([
        'https://assets.example.com/badge.png' => Http::response(badgeImageBytes(), 200, ['Content-Type' => 'image/png']),
    ]);

    $fetcher = new RemoteBadgeImageFetcher($resolver);
    $gif = app(BadgeImageNormalizer::class)->toGif($fetcher->fetch('https://assets.example.com/badge.png'));
    $info = getimagesizefromstring($gif);

    expect($info)->not->toBeFalse()
        ->and($info['mime'])->toBe('image/gif')
        ->and($info[0])->toBe(40)
        ->and($info[1])->toBe(40);

    Http::assertSentCount(1);
});

test('badge storage rejects invalid dimensions and never writes outside its disk', function () {
    Storage::fake('badges');
    $storage = app(BadgeImageStorage::class);

    expect(fn () => $storage->store('BADGE', badgeImageBytes(width: 49)))
        ->toThrow(RuntimeException::class);

    expect(Storage::disk('badges')->allFiles())->toBeEmpty();

    $storage->store('BADGE', badgeImageBytes());

    Storage::disk('badges')->assertExists('BADGE.gif');

    $permissions = fileperms(Storage::disk('badges')->path('BADGE.gif'));
    expect($permissions)->not->toBeFalse();

    $mode = (int) $permissions & 0777;
    expect($mode & 0111)->toBe(0);

    if (PHP_OS_FAMILY !== 'Windows') {
        expect($mode)->toBe(0644);
    }
});

function animatedBadgeGif(int $frames = 3): string
{
    $first = imagecreate(40, 40);
    imagecolorallocate($first, 0, 0, 0);
    ob_start();
    imagegif($first);
    $bytes = (string) ob_get_clean();
    imagedestroy($first);

    $header = substr($bytes, 0, 13 + 6);
    $frame = substr($bytes, 13 + 6, -1);
    $loop = "!\xFF\x0BNETSCAPE2.0\x03\x01\x00\x00\x00";
    $delay = "!\xF9\x04\x00\x0A\x00\x00\x00";

    return $header . $loop . str_repeat($delay . $frame, $frames) . ';';
}

test('animated gif badges keep every frame', function () {
    Storage::fake('badges');
    $gif = animatedBadgeGif(3);

    app(BadgeImageStorage::class)->store('MOVING', $gif);

    $stored = Storage::disk('badges')->get('MOVING.gif');

    expect(substr_count($stored, "!\xF9\x04"))->toBe(3)
        ->and($stored)->toContain('NETSCAPE2.0')
        ->and(getimagesizefromstring($stored)[0])->toBe(40);
});

test('gif badges lose comments and trailing data', function () {
    $gif = animatedBadgeGif(1);
    $dirty = substr($gif, 0, -1) . "!\xFE\x04evil\x00;<?php echo 1;";

    $clean = app(BadgeImageNormalizer::class)->toGif($dirty);

    expect($clean)->not->toContain('evil')->not->toContain('<?php')->toEndWith(';');
});

test('gif badges larger than one megabyte are rejected', function () {
    $gif = animatedBadgeGif(1);
    $big = substr($gif, 0, -1) . "!\xFE\xFF" . str_repeat('a', 255) . "\x00" . str_repeat("!\xFE\xFF" . str_repeat('a', 255) . "\x00", 4200) . ';';

    expect(fn () => app(BadgeImageNormalizer::class)->toGif($big))->toThrow(RuntimeException::class);
});

test('a 48x48 animated gif with many frames is stored as is', function () {
    Storage::fake('badges');
    $gif = animatedBadgeGif(80);

    app(BadgeImageStorage::class)->store('BIGCAT', $gif);

    expect(Storage::disk('badges')->get('BIGCAT.gif'))->toBe($gif);
});

<?php

namespace App\Rules;

use App\Services\Badge\BadgeImageNormalizer;
use Closure;
use ErrorException;
use Illuminate\Contracts\Validation\ValidationRule;
use Throwable;

class ValidGifBadge implements ValidationRule
{
    private const WIDTH = 40;

    private const HEIGHT = 40;

    private const MAX_BYTES = 40960;

    public const MAX_ANIMATED_BYTES = 122880;

    public const MAX_FRAMES = 24;

    /** The fastest a frame may change, in hundredths of a second. Faster flicker is not allowed. */
    public const MIN_DELAY = 5;

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $decoded = self::decode(is_string($value) ? $value : '');

        if ($decoded === null) {
            $fail('The badge image is not valid base64 data.');

            return;
        }

        if (strlen($decoded) > self::MAX_ANIMATED_BYTES) {
            $fail('The badge image is too large.');

            return;
        }

        $info = $this->imageInfo($decoded);

        if ($info === false || $info['mime'] !== 'image/gif' || $info[0] !== self::WIDTH || $info[1] !== self::HEIGHT) {
            $fail('The badge must be a 40x40 GIF image.');

            return;
        }

        $delays = self::delays($decoded);

        if ($delays === null) {
            $fail('The badge must be a 40x40 GIF image.');

            return;
        }

        if (count($delays) <= 1) {
            if (strlen($decoded) > self::MAX_BYTES) {
                $fail('The badge image is too large.');
            }

            return;
        }

        if (count($delays) > self::MAX_FRAMES) {
            $fail('An animated badge can have at most ' . self::MAX_FRAMES . ' frames.');

            return;
        }

        if (min($delays) < self::MIN_DELAY) {
            $fail('Each frame of an animated badge must last at least 50 milliseconds.');
        }
    }

    /** @return list<int>|null */
    public static function delays(string $bytes): ?array
    {
        try {
            return app(BadgeImageNormalizer::class)->frameDelays($bytes);
        } catch (Throwable) {
            return null;
        }
    }

    /**
     * Strip the optional data-URI prefix and decode, returning null on failure.
     */
    public static function decode(string $value): ?string
    {
        $stripped = preg_replace('#^data:image/\w+;base64,#i', '', $value);

        if ($stripped === null) {
            return null;
        }

        $decoded = base64_decode($stripped, true);

        return $decoded === false ? null : $decoded;
    }

    /** @return array<int|string, int|string>|false */
    private function imageInfo(string $bytes): array|false
    {
        set_error_handler(static function (int $severity, string $message): never {
            throw new ErrorException($message, severity: $severity);
        });

        try {
            return getimagesizefromstring($bytes);
        } catch (Throwable) {
            return false;
        } finally {
            restore_error_handler();
        }
    }
}

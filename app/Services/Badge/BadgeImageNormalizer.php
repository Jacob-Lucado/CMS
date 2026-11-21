<?php

namespace App\Services\Badge;

use RuntimeException;

class BadgeImageNormalizer
{
    public const MAX_BYTES = 1_048_576;

    private const MIN_SIZE = 40;

    private const MAX_SIZE = 48;

    public function toGif(string $bytes): string
    {
        if ($bytes === '' || strlen($bytes) > self::MAX_BYTES) {
            throw new RuntimeException('The badge image is empty or too large.');
        }

        $info = getimagesizefromstring($bytes);

        if (
            $info === false
            || ! in_array($info['mime'], ['image/gif', 'image/png', 'image/jpeg'], true)
            || $info[0] < self::MIN_SIZE || $info[0] > self::MAX_SIZE
            || $info[1] < self::MIN_SIZE || $info[1] > self::MAX_SIZE
        ) {
            throw new RuntimeException('Badge images must be GIF, PNG, or JPEG files between 40x40 and 48x48.');
        }

        if ($info['mime'] === 'image/gif') {
            return $this->cleanGif($bytes);
        }

        $image = imagecreatefromstring($bytes);

        if ($image === false) {
            throw new RuntimeException('The badge image could not be decoded.');
        }

        $bufferLevel = ob_get_level();
        $gif = null;
        ob_start();

        try {
            if (! imagegif($image)) {
                throw new RuntimeException('The badge image could not be encoded.');
            }

            $gif = ob_get_clean();
        } finally {
            while (ob_get_level() > $bufferLevel) {
                ob_end_clean();
            }

            imagedestroy($image);
        }

        if (! is_string($gif) || $gif === '') {
            throw new RuntimeException('The badge image could not be encoded.');
        }

        return $gif;
    }

    /**
     * Keeps every frame of an animated GIF. Only the parts the client needs are copied, so
     * comments and any data after the end of the image are dropped.
     */
    private function cleanGif(string $bytes): string
    {
        $length = strlen($bytes);

        if ($length < 14 || ! in_array(substr($bytes, 0, 6), ['GIF87a', 'GIF89a'], true)) {
            throw new RuntimeException('The badge image could not be decoded.');
        }

        $flags = ord($bytes[10]);
        $position = 13 + (($flags & 0x80) ? 3 * (2 ** (($flags & 0x07) + 1)) : 0);

        if ($position > $length) {
            throw new RuntimeException('The badge image could not be decoded.');
        }

        $clean = substr($bytes, 0, $position);
        $frames = 0;

        while ($position < $length) {
            $marker = $bytes[$position];

            if ($marker === ';') {
                if ($frames === 0) {
                    break;
                }

                return $clean . ';';
            }

            if ($marker === '!') {
                $start = $position;
                $label = ord($bytes[$position + 1] ?? "\0");
                $position = $this->skipSubBlocks($bytes, $position + 2);
                $block = substr($bytes, $start, $position - $start);

                if ($label === 0xF9 || ($label === 0xFF && preg_match('/^!\xFF\x0B(NETSCAPE2\.0|ANIMEXTS1\.0)/', $block))) {
                    $clean .= $block;
                }

                continue;
            }

            if ($marker !== ',' || $position + 10 > $length) {
                break;
            }

            $start = $position;
            $local = ord($bytes[$position + 9]);
            $position += 10 + (($local & 0x80) ? 3 * (2 ** (($local & 0x07) + 1)) : 0) + 1;
            $position = $this->skipSubBlocks($bytes, $position);
            $clean .= substr($bytes, $start, $position - $start);
            $frames++;
        }

        throw new RuntimeException('The badge image could not be decoded.');
    }

    private function skipSubBlocks(string $bytes, int $position): int
    {
        $length = strlen($bytes);

        while ($position < $length) {
            $size = ord($bytes[$position]);
            $position += $size + 1;

            if ($size === 0) {
                return $position;
            }
        }

        throw new RuntimeException('The badge image could not be decoded.');
    }
}

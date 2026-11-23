<?php

/**
 * Writes the GIF files the script checks read: a patterned still (plain and interlaced), a three frame
 * animation with transparency and disposal, and a 30 frame animation. Returns the directory.
 */
function writeGifFixtures(string $directory): string
{
    if (! is_dir($directory)) {
        mkdir($directory, 0777, true);
    }

    $still = function (bool $interlace): string {
        $image = imagecreate(57, 43);
        $colors = [];

        for ($i = 0; $i < 12; $i++) {
            $colors[] = imagecolorallocate($image, $i * 21, 255 - $i * 17, ($i * 97) % 256);
        }

        for ($y = 0; $y < 43; $y++) {
            for ($x = 0; $x < 57; $x++) {
                imagesetpixel($image, $x, $y, $colors[($x * 3 + $y * 5 + intdiv($x * $y, 7)) % 12]);
            }
        }

        if ($interlace) {
            imageinterlace($image, true);
        }

        ob_start();
        imagegif($image);

        return (string) ob_get_clean();
    };

    file_put_contents("{$directory}/plain.gif", $still(false));
    file_put_contents("{$directory}/interlaced.gif", $still(true));

    $image = imagecreatefromgif("{$directory}/plain.gif");
    $pixels = [];

    for ($y = 0; $y < 43; $y++) {
        for ($x = 0; $x < 57; $x++) {
            $color = imagecolorsforindex($image, imagecolorat($image, $x, $y));
            array_push($pixels, $color['red'], $color['green'], $color['blue']);
        }
    }

    file_put_contents("{$directory}/plain.json", json_encode($pixels));

    // Codes of three bits with a clear code before each one, so no real compression is needed.
    $compress = function (array $indexes): string {
        $bits = '';
        $emit = function (int $code) use (&$bits): void {
            $bits .= strrev(str_pad(decbin($code), 3, '0', STR_PAD_LEFT));
        };
        $emit(4);

        foreach ($indexes as $index) {
            $emit($index);
            $emit(4);
        }

        $emit(5);
        $bytes = '';

        foreach (str_split($bits, 8) as $chunk) {
            $bytes .= chr(bindec(strrev(str_pad($chunk, 8, '0', STR_PAD_RIGHT))));
        }

        return $bytes;
    };

    $frame = function (int $left, int $top, int $width, int $height, array $indexes, int $delay, int $disposal, int $transparent = -1) use ($compress): string {
        $control = "\x21\xF9\x04" . chr(($disposal << 2) | ($transparent >= 0 ? 1 : 0)) . pack('v', $delay) . chr(max($transparent, 0)) . "\x00";
        $data = $compress($indexes);

        return $control . "\x2C" . pack('vvvv', $left, $top, $width, $height) . "\x00" . "\x02" . chr(strlen($data)) . $data . "\x00";
    };

    $header = 'GIF89a' . pack('vv', 4, 4) . "\xF1\x00\x00" . "\xFF\x00\x00" . "\x00\xFF\x00" . "\x00\x00\xFF" . "\x00\x00\x00";

    file_put_contents("{$directory}/anim.gif", $header
        . $frame(0, 0, 4, 4, array_fill(0, 16, 0), 10, 1)
        . $frame(1, 1, 2, 2, [1, 3, 3, 1], 20, 2, 3)
        . $frame(0, 0, 4, 4, array_fill(0, 16, 2), 0, 0)
        . ';');

    $many = $header;

    for ($i = 0; $i < 30; $i++) {
        $many .= $frame(0, 0, 4, 4, array_fill(0, 16, $i % 3), 10, 1);
    }

    file_put_contents("{$directory}/many.gif", $many . ';');

    return $directory;
}

<?php

use App\Models\User;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;

beforeEach(function () {
    installHotel();
    $directory = storage_path('framework/testing/badges');
    File::ensureDirectoryExists($directory);
    setSetting('badge_path_filesystem', $directory);
});

/** @return list<string> the inline scripts of a page */
function inlineScripts(string $html): array
{
    preg_match_all('#<script>(.*?)</script>#s', $html, $matches);

    return $matches[1];
}

test('the badge drawer offers frames and animated gif import in every theme', function (string $theme) {
    setSetting('theme', $theme);

    $html = $this->actingAs(User::factory()->create())->get(route('draw-badge'))->assertOk()->getContent();

    expect($html)
        ->toContain('gif-reader.js')
        ->toContain('function badgeDrawer')
        ->toContain('addFrame(true)')
        ->toContain('togglePlay()')
        ->toContain('accept="image/png,image/gif"')
        ->toContain('Import PNG or GIF');
})->with(['atom', 'dusk']);

test('the drawer script on the page is valid javascript', function (string $theme) {
    if (Process::run('node --version')->failed()) {
        $this->markTestSkipped('Node is not installed.');
    }

    setSetting('theme', $theme);
    $html = $this->actingAs(User::factory()->create())->get(route('draw-badge'))->assertOk()->getContent();
    $directory = sys_get_temp_dir() . '/drawer-' . $theme . '-' . getmypid();
    File::ensureDirectoryExists($directory);

    foreach (inlineScripts($html) as $index => $script) {
        File::put("{$directory}/script{$index}.js", $script);
        $result = Process::run("node --check {$directory}/script{$index}.js");

        expect($result->successful())->toBeTrue($result->errorOutput());
    }

    File::deleteDirectory($directory);
})->with(['atom', 'dusk']);

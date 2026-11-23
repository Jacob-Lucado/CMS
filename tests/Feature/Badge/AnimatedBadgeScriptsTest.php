<?php

use App\Models\User;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;

require_once __DIR__ . '/../../js/fixtures.php';

beforeEach(function () {
    if (Process::run('node --version')->failed()) {
        $this->markTestSkipped('Node is not installed.');
    }

    $this->scratch = sys_get_temp_dir() . '/badge-scripts-' . getmypid() . '-' . bin2hex(random_bytes(3));
    writeGifFixtures($this->scratch);
});

afterEach(function () {
    File::deleteDirectory($this->scratch);
});

test('the gif reader decodes stills, interlaced pictures and animations', function () {
    $result = Process::run(['node', base_path('tests/js/gif-reader-check.cjs'), public_path('assets/js/gif/gif-reader.js'), $this->scratch]);

    expect($result->successful())->toBeTrue($result->errorOutput() . $result->output())
        ->and($result->output())->toContain('gif reader ok');
});

test('the badge drawer edits frames, imports gifs and exports animations', function (string $theme) {
    installHotel();
    setSetting('theme', $theme);
    setSetting('badge_path_filesystem', storage_path('framework/testing/badges'));
    File::ensureDirectoryExists(storage_path('framework/testing/badges'));

    $html = $this->actingAs(User::factory()->create())->get(route('draw-badge'))->assertOk()->getContent();
    preg_match_all('#<script>(.*?)</script>#s', $html, $matches);
    $script = collect($matches[1])->first(fn (string $code): bool => str_contains($code, 'function badgeDrawer'));

    expect($script)->not->toBeNull();
    File::put("{$this->scratch}/drawer.js", $script);

    $result = Process::run(['node', base_path('tests/js/drawer-check.cjs'), "{$this->scratch}/drawer.js", public_path('assets/js/gif/gif-reader.js'), $this->scratch]);

    expect($result->successful())->toBeTrue($result->errorOutput() . $result->output())
        ->and($result->output())->toContain('drawer ok');
})->with(['atom', 'dusk']);

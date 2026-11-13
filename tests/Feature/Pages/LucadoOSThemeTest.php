<?php

use App\Console\Commands\AtomInstallCommand;
use App\Models\User;
use App\Services\ViteService;
use Illuminate\Console\OutputStyle;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;

beforeEach(function () {
    installHotel();
    setSetting('theme', 'lucadoos');
    setSetting('enable_caching', '0');
});

test('a normal visit gets the desktop with the page in its first window', function () {
    $this->get(route('welcome'))
        ->assertOk()
        ->assertSee('id="los-desk"', false)
        ->assertSee('id="los-data"', false)
        ->assertSee('LucadoOS™ is a trademark of Jacob Lucado.');
});

test('a page opened in a window gets just the page', function (array $request) {
    $this->get(route('welcome') . ($request['query'] ?? ''), $request['headers'] ?? [])
        ->assertOk()
        ->assertSee('los-embed', false)
        ->assertDontSee('id="los-desk"', false);
})->with([
    'frame request' => [['headers' => ['Sec-Fetch-Dest' => 'iframe']]],
    'embed query' => [['query' => '?los=embed']],
]);

test('the desktop lists pages for the signed in player', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->get(route('me.show'))
        ->assertOk()
        ->assertSee('id="los-desk"', false)
        ->assertSee($user->username)
        ->assertSee(parse_url(route('shop.index'), PHP_URL_PATH));
});

test('CMS_THEME wins over the theme setting', function () {
    setSetting('theme', 'dusk');
    config(['theme.pinned' => 'lucadoos']);

    expect(setting('theme'))->toBe('lucadoos');

    $this->get(route('welcome'))->assertOk()->assertSee('id="los-desk"', false);
});

test('a CMS_THEME that names no theme is ignored', function (string $pinned) {
    setSetting('theme', 'dusk');
    config(['theme.pinned' => $pinned]);

    expect(setting('theme'))->toBe('dusk');
})->with(['missing theme' => 'nope', 'path outside the themes' => '../views']);

test('atom:install keeps the pinned theme when an installer asks for another', function () {
    Process::fake();
    setSetting('theme', 'atom');
    config(['theme.pinned' => 'lucadoos']);

    $command = app(AtomInstallCommand::class);
    $command->setLaravel(app());
    $input = new ArrayInput(['--theme' => 'dusk', '--skip-build' => true], $command->getDefinition());
    $input->setInteractive(false);
    $command->setInput($input);
    $command->setOutput(new OutputStyle($input, new BufferedOutput));

    (new ReflectionMethod($command, 'setUpTheme'))->invoke($command);

    expect(DB::table('website_settings')->where('key', 'theme')->value('value'))->toBe('lucadoos');
});

test('a theme with its own build folder loads its assets from there', function () {
    $directory = public_path('build-vitetest');
    File::ensureDirectoryExists($directory);
    File::put($directory . '/manifest.json', json_encode([
        'resources/themes/vitetest/css/app.css' => ['file' => 'assets/app-test.css', 'src' => 'resources/themes/vitetest/css/app.css', 'isEntry' => true],
    ]));

    try {
        $tags = (string) (new ViteService)(['resources/themes/vitetest/css/app.css'], 'build');
    } finally {
        File::deleteDirectory($directory);
    }

    expect($tags)->toContain('/build-vitetest/assets/app-test.css');
});

<?php

use App\Emulator\Data\Feature;
use App\Emulator\Emulator;
use App\Emulator\EmulatorManager;
use App\Models\User;
use Symfony\Component\Finder\Finder;
use Tests\Fixtures\ThirdEmulatorDriver;

test('arcturus supports every feature and ada only what it stores', function () {
    app(EmulatorManager::class)->select('arcturus');

    foreach (Feature::cases() as $feature) {
        expect(Emulator::supports($feature))->toBeTrue("arcturus should support {$feature->value}");
    }

    useAdaSchema();

    expect(Emulator::supports(Feature::RareValues))->toBeTrue()
        ->and(Emulator::supports(Feature::CameraPhotos))->toBeFalse()
        ->and(Emulator::supports(Feature::NameChangePermission))->toBeFalse()
        ->and(Emulator::supports(Feature::CommandLogs))->toBeFalse()
        ->and(Emulator::supports(Feature::Wordfilter))->toBeFalse();
});

test('every feature gates something outside the drivers that declare it', function (Feature $feature) {
    // A Feature nothing reads is configuration that lies about what a driver
    // does. Drivers only declare support, so their own files do not count.
    $finder = Finder::create()
        ->files()
        ->in([app_path(), base_path('routes')])
        ->notPath('Emulator/Drivers')
        ->notPath('Emulator/Data')
        ->name('*.php')
        ->contains(sprintf('/Feature::%s\b|%s/', $feature->name, preg_quote($feature->value, '/')));

    expect(iterator_count($finder))
        ->toBeGreaterThan(0, "Feature::{$feature->name} is declared but never read");
})->with(Feature::cases());

test('gated routes early-return on drivers without the feature', function () {
    installHotel();

    $user = User::factory()->create();

    $this->actingAs($user)->get(route('values.index'))->assertOk();

    config(['emulator.drivers.third' => ThirdEmulatorDriver::class]);
    app(EmulatorManager::class)->select('third');

    $this->actingAs($user)
        ->get(route('values.index'))
        ->assertRedirect(route('welcome'));
});

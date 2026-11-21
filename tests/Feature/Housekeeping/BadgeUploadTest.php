<?php

use App\Filament\Resources\Hotel\BadgeUploads\Pages\ManageBadgeUploads;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

beforeEach(function () {
    installHotel();
    setSetting('force_staff_2fa', '0');
    Filament::setCurrentPanel(Filament::getPanel('housekeeping'));
    Storage::fake('badges');
});

function uploadBadge(string $name, string $contents)
{
    return Livewire::actingAs(User::factory()->create(['rank' => 7]))
        ->test(ManageBadgeUploads::class)
        ->set('badge_file', UploadedFile::fake()->createWithContent($name, $contents))
        ->call('save');
}

test('an animated gif badge uploads with all of its frames', function () {
    $cat = file_get_contents(base_path('tests/Fixtures/dancing-cat.gif'));

    uploadBadge('CAT01.gif', $cat)->assertHasNoErrors();

    $stored = Storage::disk('badges')->get('CAT01.gif');

    expect(strlen($cat))->toBeGreaterThan(65536)
        ->and(substr_count($stored, "\x21\xF9\x04"))->toBe(substr_count($cat, "\x21\xF9\x04"));
});

test('a badge larger than one megabyte is refused', function () {
    $big = file_get_contents(base_path('tests/Fixtures/dancing-cat.gif')) . str_repeat('a', 1_100_000);

    uploadBadge('CAT02.gif', $big)->assertHasErrors('badge_file');

    Storage::disk('badges')->assertMissing('CAT02.gif');
});

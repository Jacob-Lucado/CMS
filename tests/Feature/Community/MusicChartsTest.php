<?php

use App\Models\RoomStreamPlay;
use App\Models\User;

beforeEach(function () {
    installHotel();
});

function play(string $by, string $url, string $when = 'now', string $kind = 'youtube'): void
{
    RoomStreamPlay::query()->create(['room_id' => 1, 'set_by' => $by, 'kind' => $kind, 'url' => $url, 'played_at' => now()->modify($when)]);
}

test('the music charts page renders with no plays', function () {
    $this->actingAs(User::factory()->create())->get(route('music.index'))
        ->assertOk()
        ->assertSee('No songs have been played yet');
});

test('the charts rank songs and DJs from the last week', function () {
    play('Alice', 'https://www.youtube.com/watch?v=aaa');
    play('Alice', 'https://www.youtube.com/watch?v=aaa');
    play('Bob', 'https://www.youtube.com/watch?v=aaa');
    play('Bob', 'https://www.youtube.com/watch?v=bbb');
    play('Old', 'https://www.youtube.com/watch?v=old', '-10 days');

    $response = $this->actingAs(User::factory()->create())->get(route('music.index'))->assertOk();

    $response->assertSeeInOrder(['youtube.com/watch?v=aaa', '3 plays', 'youtube.com/watch?v=bbb', '1 play']);
    $response->assertSeeInOrder(['Alice', '2 songs', 'Bob', '2 songs']);
    expect(strpos($response->getContent(), 'Top songs'))->toBeLessThan(strpos($response->getContent(), 'Top DJs'));
});

test('a song link is only clickable over https', function () {
    play('Alice', 'https://example.com/song');
    play('Alice', 'javascript:alert(1)');

    $html = $this->actingAs(User::factory()->create())->get(route('music.index'))->assertOk()->getContent();

    expect($html)->toContain('href="https://example.com/song"')
        ->and($html)->not->toContain('href="javascript:');
});

test('song text is escaped', function () {
    play('<b>Eve</b>', 'https://example.com/<script>x</script>');

    $this->actingAs(User::factory()->create())->get(route('music.index'))
        ->assertOk()
        ->assertDontSee('<b>Eve</b>', false)
        ->assertDontSee('<script>x</script>', false);
});

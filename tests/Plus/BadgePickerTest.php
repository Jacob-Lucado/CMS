<?php

use App\Filament\Forms\BadgePicker;
use Illuminate\Support\Facades\DB;

test('the badge picker lists matching badges and still takes a code that is not listed', function () {
    DB::table('badge_definitions')->insert([
        ['code' => 'ADM', 'required_right' => ''],
        ['code' => 'ACH_RoomEntry1', 'required_right' => ''],
        ['code' => 'XMAS1', 'required_right' => ''],
    ]);

    expect(array_keys(BadgePicker::options('ad')))->toBe(['ad', 'ADM'])
        ->and(array_keys(BadgePicker::options('ACH_RoomEntry1')))->toBe(['ACH_RoomEntry1'])
        ->and(array_keys(BadgePicker::options('100%')))->toBe([])
        ->and(BadgePicker::label('ADM'))->toContain('ADM.gif');
});

<?php

use App\Filament\Resources\Hotel\AdaCatalogItems\AdaCatalogItemResource;
use App\Filament\Resources\Hotel\AdaCatalogPages\AdaCatalogPageResource;
use App\Filament\Resources\Hotel\AdaPlayerMessages\AdaPlayerMessageResource;
use App\Filament\Resources\Hotel\AdaRoles\AdaRoleResource;
use App\Filament\Resources\Hotel\AdaRoomChatMessages\AdaRoomChatMessageResource;
use App\Filament\Resources\Hotel\AdaServerLocaleTexts\AdaServerLocaleTextResource;
use App\Filament\Resources\Hotel\AdaServerSettings\AdaServerSettingsResource;
use App\Filament\Resources\User\AdaIpBans\AdaIpBanResource;
use App\Filament\Resources\User\AdaPlayerBans\AdaPlayerBanResource;
use App\Models\User;

/**
 * Ada's housekeeping resources are only reachable on the Ada driver, so the
 * shared panel tests never touch them. These render each one for real: a
 * broken table, form or policy would otherwise ship unnoticed.
 */
beforeEach(function () {
    installHotel();
    setSetting('force_staff_2fa', '0');

    // Panel access sits below the resource permissions, so the negative case
    // reaches housekeeping and is turned away by the resource policy itself
    // rather than by the panel gate.
    grantHousekeepingPermission('can_access_housekeeping', 1);

    foreach ([
        'manage_bans',
        'manage_catalog_pages',
        'manage_permissions',
        'manage_private_chatlogs',
        'manage_room_chatlogs',
        'manage_emulator_texts',
        'manage_emulator_settings',
    ] as $permission) {
        grantHousekeepingPermission($permission, 6);
    }

});

dataset('ada resources', [
    'player bans' => AdaPlayerBanResource::class,
    'catalog pages' => AdaCatalogPageResource::class,
    'catalog items' => AdaCatalogItemResource::class,
    'ip bans' => AdaIpBanResource::class,
    'roles' => AdaRoleResource::class,
    'room chatlogs' => AdaRoomChatMessageResource::class,
    'private messages' => AdaPlayerMessageResource::class,
    'server settings' => AdaServerSettingsResource::class,
    'emulator texts' => AdaServerLocaleTextResource::class,
]);

test('ada housekeeping resources render for a permitted staff member', function (string $resource) {
    $staff = User::factory()->create(['rank' => 6]);

    $this->actingAs($staff)
        ->get($resource::getUrl('index'))
        ->assertOk();
})->with('ada resources');

test('ada housekeeping resources stay closed to staff without the permission', function (string $resource) {
    $staff = User::factory()->create(['rank' => 5]);

    $this->actingAs($staff)
        ->get($resource::getUrl('index'))
        ->assertForbidden();
})->with('ada resources');

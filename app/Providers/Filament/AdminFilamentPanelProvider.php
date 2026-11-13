<?php

namespace App\Providers\Filament;

use App\Filament\Auth\FortifyTwoFactorAuthentication;
use App\Filament\Pages\Login;
use App\Http\Middleware\AuthenticateHousekeepingSession;
use App\Http\Middleware\BannedMiddleware;
use App\Http\Middleware\ForceStaffTwoFactorMiddleware;
use App\Http\Middleware\MaintenanceMiddleware;
use Filament\Enums\ThemeMode;
use Filament\FontProviders\LocalFontProvider;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets\AccountWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminFilamentPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('housekeeping')
            ->path('housekeeping')
            ->strictAuthorization()
            ->login(Login::class)
            ->multiFactorAuthentication(app(FortifyTwoFactorAuthentication::class))
            ->viteTheme('resources/css/filament/housekeeping/theme.css', 'build-housekeeping')
            // LucadoOS: its blue, and grays that lean navy like the rest of the site.
            ->colors([
                'primary' => Color::hex('#4da3ff'),
                'info' => Color::hex('#7c6cff'),
                'gray' => [
                    50 => 'oklch(0.97 0.008 250)',
                    100 => 'oklch(0.94 0.012 250)',
                    200 => 'oklch(0.88 0.02 250)',
                    300 => 'oklch(0.8 0.028 250)',
                    400 => 'oklch(0.66 0.035 250)',
                    500 => 'oklch(0.55 0.04 252)',
                    600 => 'oklch(0.42 0.045 254)',
                    700 => 'oklch(0.32 0.045 256)',
                    800 => 'oklch(0.24 0.04 258)',
                    900 => 'oklch(0.19 0.035 258)',
                    950 => 'oklch(0.15 0.03 260)',
                ],
            ])
            ->brandName(fn (): string => sprintf('%s Housekeeping', setting('hotel_name') ?: 'Hotel'))
            ->defaultThemeMode(ThemeMode::Dark)
            ->font('system-ui', provider: LocalFontProvider::class)
            ->sidebarCollapsibleOnDesktop()
            ->navigationGroups(['Dashboard', 'Hotel', 'User Management', 'Shop Management', 'Website', 'Profile Management', 'Logs'])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                AccountWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateHousekeepingSession::class,
                ShareErrorsFromSession::class,
                PreventRequestForgery::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->persistentMiddleware([
                AuthenticateHousekeepingSession::class,
            ])
            ->authMiddleware([
                Authenticate::class,
                BannedMiddleware::class,
                MaintenanceMiddleware::class,
                ForceStaffTwoFactorMiddleware::class,
            ], isPersistent: true)
            ->plugins([]);
    }
}

<?php

namespace App\Filament\Widgets;

use App\Emulator\Contracts\PlayerRepository;
use App\Filament\Pages\HotelActions;
use App\Filament\Resources\Atom\Articles\ArticleResource;
use App\Filament\Resources\Atom\Tickets\TicketResource;
use App\Filament\Resources\Hotel\MusicPlays\MusicPlayResource;
use App\Filament\Resources\User\Users\UserResource;
use App\Models\User;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Cache;

class WelcomeBanner extends Widget
{
    protected static ?int $sort = -10;

    protected string $view = 'filament.widgets.welcome-banner';

    protected int|string|array $columnSpan = 'full';

    protected ?string $pollingInterval = '60s';

    public function greeting(): string
    {
        $hour = (int) now()->format('G');

        return match (true) {
            $hour < 5 => __('Still up'),
            $hour < 12 => __('Good morning'),
            $hour < 18 => __('Good afternoon'),
            default => __('Good evening'),
        };
    }

    public function name(): string
    {
        return (string) auth()->user()?->username;
    }

    public function online(): int
    {
        return Cache::remember('housekeeping.dashboard.online', 30, fn (): int => app(PlayerRepository::class)->whereOnline(User::query())->count());
    }

    /** @return list<array{label: string, icon: string, url: string}> */
    public function links(): array
    {
        $links = [];

        if (HotelActions::canAccess()) {
            $links[] = ['label' => __('Alert the hotel'), 'icon' => 'heroicon-o-megaphone', 'url' => HotelActions::getUrl()];
        }

        if (ArticleResource::canCreate()) {
            $links[] = ['label' => __('Write an article'), 'icon' => 'heroicon-o-pencil-square', 'url' => ArticleResource::getUrl('create')];
        }

        if (TicketResource::canViewAny()) {
            $links[] = ['label' => __('Open tickets'), 'icon' => 'heroicon-o-lifebuoy', 'url' => TicketResource::getUrl()];
        }

        if (UserResource::canViewAny()) {
            $links[] = ['label' => __('Find a player'), 'icon' => 'heroicon-o-magnifying-glass', 'url' => UserResource::getUrl()];
        }

        if (MusicPlayResource::canViewAny() && MusicPlayResource::shouldRegisterNavigation()) {
            $links[] = ['label' => __('Room music'), 'icon' => 'heroicon-o-musical-note', 'url' => MusicPlayResource::getUrl()];
        }

        return $links;
    }
}

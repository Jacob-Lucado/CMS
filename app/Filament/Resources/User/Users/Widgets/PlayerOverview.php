<?php

namespace App\Filament\Resources\User\Users\Widgets;

use App\Emulator\Emulator;
use App\Models\User;
use Filament\Widgets\Widget;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * The things staff look up about a player most: their Discord link, where they connect from,
 * and other accounts that share those addresses.
 */
class PlayerOverview extends Widget
{
    protected string $view = 'filament.widgets.player-overview';

    protected int|string|array $columnSpan = 'full';

    public ?Model $record = null;

    public function player(): User
    {
        /** @var User $player */
        $player = $this->record;

        return $player->loadMissing('discordAccount');
    }

    /** @return Collection<int, User> */
    public function sharedAddressAccounts(): Collection
    {
        $player = $this->player();
        $addresses = collect([$player->ip_register, $player->ip_current])
            ->filter(fn ($ip): bool => is_string($ip) && $ip !== '' && $ip !== '127.0.0.1')
            ->unique()
            ->values();

        if ($addresses->isEmpty()) {
            return collect();
        }

        $registered = $this->column('ip_register');
        $current = $this->column('ip_current');

        return User::query()
            ->whereKeyNot($player->getKey())
            ->where(fn ($query) => $query->whereIn($registered, $addresses)->orWhereIn($current, $addresses))
            ->limit(25)
            ->get();
    }

    private function column(string $attribute): string
    {
        return (new User)->qualifyColumn(Emulator::playerSchema()->columns[$attribute] ?? $attribute);
    }
}

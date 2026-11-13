<?php

namespace App\Services\Hotel;

use App\Contracts\Rcon;
use App\Emulator\Contracts\PlayerRepository;
use App\Emulator\Emulator;
use App\Enums\CurrencyTypes;
use App\Exceptions\RconConnectionException;
use App\Models\User;
use Closure;
use Illuminate\Support\Facades\Log;

/**
 * Hotel-wide actions for Housekeeping. Things done "to everyone" go to each player who is online, one at a time,
 * through the game server, so they show up in the hotel straight away.
 */
class HotelControl
{
    /** More than this many players is a job for a queue, not a button. */
    public const MAX_PLAYERS = 2000;

    /** Reloads only PlusEMU offers, by the game server's own command name. */
    private const PLUS_RELOADS = [
        'items' => 'reload_items',
        'navigator' => 'reload_navigator',
        'bans' => 'reload_bans',
        'ranks' => 'reload_ranks',
        'server_settings' => 'reload_server_settings',
    ];

    public function __construct(
        private readonly Rcon $rcon,
        private readonly PlayerRepository $players,
    ) {}

    public function isGameServerOnline(): bool
    {
        return $this->rcon->isConnected();
    }

    public function onlineCount(): int
    {
        return $this->players->whereOnline(User::query())->count();
    }

    /** @return array{sent: int, failed: int} */
    public function alertEveryone(string $message): array
    {
        return $this->eachOnline(fn (User $user) => $this->rcon->alertUser($user, $message));
    }

    /** @return array{sent: int, failed: int} */
    public function giveEveryoneBadge(string $badge): array
    {
        return $this->eachOnline(fn (User $user) => $this->rcon->giveBadge($user, $badge));
    }

    /** @return array{sent: int, failed: int} */
    public function giveEveryoneCurrency(CurrencyTypes $currency, int $amount): array
    {
        return $this->eachOnline(fn (User $user) => $this->rcon->giveCurrency($user, $currency, $amount));
    }

    /**
     * What this hotel's game server can reload, keyed for reload().
     *
     * @return array<string, string>
     */
    public function reloads(): array
    {
        $reloads = [
            'catalog' => __('Catalog'),
            'filter' => __('Word filter'),
        ];

        if (Emulator::driver() === 'plus') {
            $reloads += [
                'items' => __('Furniture'),
                'navigator' => __('Navigator'),
                'bans' => __('Bans'),
                'ranks' => __('Ranks and permissions'),
                'server_settings' => __('Server settings'),
            ];
        }

        return $reloads;
    }

    /**
     * @throws RconConnectionException
     */
    public function reload(string $what): void
    {
        match (true) {
            $what === 'catalog' => $this->rcon->updateCatalog(),
            $what === 'filter' => $this->rcon->updateWordFilter(),
            Emulator::driver() === 'plus' && isset(self::PLUS_RELOADS[$what]) => $this->sendPlusReload(self::PLUS_RELOADS[$what]),
            default => throw new RconConnectionException("This game server cannot reload {$what}"),
        };
    }

    private function sendPlusReload(string $command): void
    {
        $response = $this->rcon->sendCommand($command, []);

        if (! $response->successful()) {
            throw new RconConnectionException("The game server refused {$command}: {$response->message}");
        }
    }

    /**
     * @param  Closure(User): void  $send
     *
     * @return array{sent: int, failed: int}
     */
    private function eachOnline(Closure $send): array
    {
        $result = ['sent' => 0, 'failed' => 0];

        $this->players->whereOnline(User::query())
            ->limit(self::MAX_PLAYERS)
            ->get()
            ->each(function (User $user) use ($send, &$result): void {
                try {
                    $send($user);
                    $result['sent']++;
                } catch (RconConnectionException $exception) {
                    $result['failed']++;
                    Log::warning('Hotel-wide action skipped a player', ['user_id' => $user->id, 'reason' => $exception->getMessage()]);
                }
            });

        return $result;
    }
}

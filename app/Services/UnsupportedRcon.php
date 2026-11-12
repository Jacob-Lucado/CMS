<?php

namespace App\Services;

use App\Contracts\Rcon;
use App\Data\RconResponse;
use App\Enums\CurrencyTypes;
use App\Exceptions\RconConnectionException;
use App\Models\User;

/**
 * Stands in for emulator drivers that have no RCON protocol.
 *
 * isConnected() always reports false, which is the check every call site makes
 * before reaching for RCON; those call sites fall back to the driver's
 * database repositories. Every send therefore throws rather than silently
 * succeeding, so a missing guard surfaces as an error instead of a grant that
 * quietly disappears.
 */
class UnsupportedRcon implements Rcon
{
    public function __construct(private readonly string $driver) {}

    public function isConnected(): bool
    {
        return false;
    }

    public function sendCommand(string $command, ?array $data = null): RconResponse
    {
        $this->unsupported();
    }

    public function sendGift(User $user, int $itemId, string $message = 'Here is a gift.'): void
    {
        $this->unsupported();
    }

    public function giveCurrency(User $user, CurrencyTypes $currency, int $amount): void
    {
        $this->unsupported();
    }

    public function giveBadge(User $user, string $badge): void
    {
        $this->unsupported();
    }

    public function setMotto(User $user, string $motto): void
    {
        $this->unsupported();
    }

    public function updateWordFilter(): void
    {
        $this->unsupported();
    }

    public function disconnectUser(User $user): void
    {
        $this->unsupported();
    }

    public function setRank(User $user, int $rank): void
    {
        $this->unsupported();
    }

    public function updateCatalog(): void
    {
        $this->unsupported();
    }

    public function alertUser(User $user, string $message): void
    {
        $this->unsupported();
    }

    public function forwardUser(User $user, int $roomId): void
    {
        $this->unsupported();
    }

    public function updateConfig(User $user, string $command): void
    {
        $this->unsupported();
    }

    /**
     * @throws RconConnectionException
     */
    private function unsupported(): never
    {
        throw new RconConnectionException("RCON is not supported by emulator driver [{$this->driver}]");
    }
}

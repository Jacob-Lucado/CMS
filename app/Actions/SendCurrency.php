<?php

namespace App\Actions;

use App\Contracts\Rcon;
use App\Emulator\Contracts\CurrencyRepository;
use App\Enums\CurrencyTypes;
use App\Exceptions\CurrencyGrantException;
use App\Models\User;

class SendCurrency
{
    public function __construct(
        private readonly Rcon $rcon,
        private readonly CurrencyRepository $currencies,
    ) {}

    /**
     * Adjust a player's currency by a signed amount: live via Rcon when the
     * emulator is online, otherwise straight to the database.
     *
     * @throws CurrencyGrantException when neither route is safe
     */
    public function execute(User $user, CurrencyTypes $currency, int $amount): void
    {
        if ($amount === 0) {
            return;
        }

        if ($this->rcon->isConnected()) {
            $this->rcon->giveCurrency($user, $currency, $amount);

            return;
        }

        // A player in game holds their balance in memory and writes it back on
        // disconnect, so a database grant made underneath them is discarded.
        // Drivers without an RCON bridge are always in this position, which is
        // why this is a refusal rather than a best effort.
        if ($user->online) {
            throw new CurrencyGrantException(
                "Cannot grant {$currency->value} to online player {$user->id} without a connected RCON.",
            );
        }

        $this->currencies->give($user, $currency, $amount);
    }
}

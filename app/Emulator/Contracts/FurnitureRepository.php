<?php

namespace App\Emulator\Contracts;

use App\Emulator\Data\FurnitureHolding;
use App\Models\User;
use Illuminate\Support\Collection;

/**
 * Writes furniture into a player's inventory on the emulator database - the
 * offline path used when RCON is down. Arcturus items reference their base
 * item through item_id, while Ada uses player_furniture_items.
 */
interface FurnitureRepository
{
    public function definitionCount(): int;

    public function isLimitedEdition(int $baseItemId): bool;

    /**
     * Place copies of a base furniture item into the user's inventory.
     */
    public function grant(User $user, int $baseItemId, int $amount): void;

    /**
     * The users holding a base furniture item, ordered by quantity.
     *
     * @return Collection<int, FurnitureHolding>
     */
    public function holdings(int $baseItemId, int $limit = 100): Collection;
}

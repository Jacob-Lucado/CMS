<?php

namespace App\Emulator\Contracts;

use App\Emulator\Data\OwnedBadge;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Reads and writes a player's owned badges on the emulator database. Arcturus
 * stores them in users_badges, while Ada joins player_badges to badges;
 * drivers normalise both to OwnedBadge.
 *
 * Like currency, this is the offline path - grants while the emulator is
 * online go through Rcon so the player's session stays in sync.
 */
interface BadgeRepository
{
    /** @return HasMany<covariant Model, User> */
    public function relation(User $user): HasMany;

    /**
     * Every badge code the user owns.
     *
     * @return array<int, string>
     */
    public function codes(User $user): array;

    /**
     * Grant a badge code; granting an already-owned badge is a no-op.
     */
    public function grant(User $user, string $badge): void;

    public function revoke(User $user, string $badge): void;

    /**
     * The user's badges, newest first, for the profile badge widget.
     *
     * @return LengthAwarePaginator<int, OwnedBadge>
     */
    public function paginate(User $user, int $perPage, string $pageName): LengthAwarePaginator;
}

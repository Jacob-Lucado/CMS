<?php

namespace App\Emulator\Contracts;

use App\Emulator\Data\Photo;
use Illuminate\Support\Collection;

interface PhotoRepository
{
    /** Whether this emulator saves camera photos the website can show. */
    public function available(): bool;

    /**
     * Published photos, newest first. With a player, every photo that player took.
     *
     * @return Collection<int, Photo>
     */
    public function latest(int $offset, int $limit, ?int $userId = null): Collection;

    public function find(string $id): ?Photo;

    /**
     * @param  list<string>  $ids
     *
     * @return Collection<int, Photo>
     */
    public function findMany(array $ids): Collection;
}

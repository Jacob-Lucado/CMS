<?php

namespace App\Emulator\Drivers\Ada;

use App\Emulator\Contracts\PhotoRepository;
use Illuminate\Support\Collection;

/**
 * Ada keeps no camera photos the website can read.
 */
final class AdaPhotoRepository implements PhotoRepository
{
    public function available(): bool
    {
        return false;
    }

    public function latest(int $offset, int $limit, ?int $userId = null): Collection
    {
        return collect();
    }

    public function find(string $id): null
    {
        return null;
    }

    public function findMany(array $ids): Collection
    {
        return collect();
    }
}

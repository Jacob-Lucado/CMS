<?php

namespace App\Emulator\Drivers\Arcturus;

use App\Emulator\Contracts\PhotoRepository;
use App\Emulator\Data\Photo;
use App\Models\Miscellaneous\CameraWeb;
use Illuminate\Support\Collection;

final class ArcturusPhotoRepository implements PhotoRepository
{
    public function available(): bool
    {
        return true;
    }

    public function latest(int $offset, int $limit, ?int $userId = null): Collection
    {
        return CameraWeb::query()
            ->where('visible', 1)
            ->when($userId !== null, fn ($query) => $query->where('user_id', $userId))
            ->latest('id')
            ->offset($offset)
            ->limit($limit)
            ->get()
            ->map($this->photo(...));
    }

    public function find(string $id): ?Photo
    {
        return $this->findMany([$id])->first();
    }

    public function findMany(array $ids): Collection
    {
        $numeric = array_values(array_filter($ids, ctype_digit(...)));

        return $numeric === [] ? collect() : CameraWeb::query()->where('visible', 1)->whereIn('id', $numeric)->get()->map($this->photo(...));
    }

    private function photo(CameraWeb $photo): Photo
    {
        return new Photo((string) $photo->id, $photo->url, (int) $photo->user_id, $photo->timestamp);
    }
}

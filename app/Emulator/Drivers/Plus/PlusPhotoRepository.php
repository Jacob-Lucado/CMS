<?php

namespace App\Emulator\Drivers\Plus;

use App\Emulator\Contracts\PhotoRepository;
use App\Emulator\Data\Photo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * PlusEMU saves every photo in camera_media and lists the ones a player published in camera_publications.
 * The image lives at /camera/<id>.png.
 */
final class PlusPhotoRepository implements PhotoRepository
{
    private const UUID = '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/';

    public function available(): bool
    {
        return Schema::hasTable('camera_media') && Schema::hasTable('camera_publications');
    }

    public function latest(int $offset, int $limit, ?int $userId = null): Collection
    {
        if (! $this->available()) {
            return collect();
        }

        $query = $userId === null
            ? DB::table('camera_publications')->select(['media_id as id', 'user_id', 'created_at'])
            : DB::table('camera_media')->select(['id', 'user_id', 'created_at'])->where('user_id', $userId);

        return $query->orderByDesc('created_at')->offset($offset)->limit($limit)->get()->map($this->photo(...));
    }

    public function find(string $id): ?Photo
    {
        return $this->findMany([$id])->first();
    }

    public function findMany(array $ids): Collection
    {
        $ids = array_values(array_filter(array_map(strtolower(...), $ids), fn (string $id): bool => preg_match(self::UUID, $id) === 1));

        if ($ids === [] || ! $this->available()) {
            return collect();
        }

        return DB::table('camera_media')->select(['id', 'user_id', 'created_at'])->whereIn('id', $ids)->get()->map($this->photo(...));
    }

    private function photo(object $row): Photo
    {
        /** @var object{id: string, user_id: int|string, created_at: string|null} $row */
        return new Photo((string) $row->id, '/camera/' . $row->id . '.png', (int) $row->user_id, $row->created_at ? Carbon::parse($row->created_at) : null);
    }
}

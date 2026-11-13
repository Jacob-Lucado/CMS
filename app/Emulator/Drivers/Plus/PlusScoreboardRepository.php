<?php

namespace App\Emulator\Drivers\Plus;

use App\Emulator\Contracts\ScoreboardRepository;
use App\Emulator\Data\GameScore;
use App\Emulator\Data\Scoreboard;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * PlusEMU saves a board's scores as JSON in items.extra_data: {"Entries": [{"Score": 10, "Users": ["name"]}]}.
 */
final class PlusScoreboardRepository implements ScoreboardRepository
{
    public function scoreboards(int $limit = 200): Collection
    {
        return $this->boards()->orderBy('r.caption')->limit($limit)->get()->map($this->board(...));
    }

    public function find(int $itemId): ?Scoreboard
    {
        $row = $this->boards()->where('i.id', $itemId)->first();

        return $row === null ? null : $this->board($row);
    }

    public function top(int $itemId, int $limit = 5): Collection
    {
        $data = json_decode((string) DB::table('items')->where('id', $itemId)->value('extra_data'), true);

        return collect(is_array($data) ? (array) ($data['Entries'] ?? []) : [])
            ->filter(fn (mixed $entry): bool => is_array($entry) && isset($entry['Score']))
            ->map(fn (array $entry): GameScore => new GameScore((int) $entry['Score'], array_values(array_map(strval(...), (array) ($entry['Users'] ?? [])))))
            ->sortByDesc(fn (GameScore $score): int => $score->score)
            ->take($limit)
            ->values();
    }

    private function boards(): Builder
    {
        return DB::table('items as i')
            ->join('furniture as f', 'f.id', '=', 'i.base_item')
            ->join('rooms as r', 'r.id', '=', 'i.room_id')
            ->where('f.item_name', 'like', 'highscore%')
            ->select(['i.id', 'f.item_name', 'r.id as room_id', 'r.caption']);
    }

    private function board(object $row): Scoreboard
    {
        /** @var object{id: int, item_name: string, room_id: int, caption: string} $row */
        return new Scoreboard((int) $row->id, (string) $row->item_name, (int) $row->room_id, (string) $row->caption);
    }
}

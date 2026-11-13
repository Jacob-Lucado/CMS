<?php

namespace App\Emulator\Drivers\Arcturus;

use App\Emulator\Contracts\ScoreboardRepository;
use App\Emulator\Data\GameScore;
use App\Emulator\Data\Scoreboard;
use App\Models\User;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Arcturus keeps each board's scores in items_highscore_data, with the players as comma separated ids.
 */
final class ArcturusScoreboardRepository implements ScoreboardRepository
{
    public function scoreboards(int $limit = 200): Collection
    {
        return $this->boards()->orderBy('r.name')->limit($limit)->get()->map($this->board(...));
    }

    public function find(int $itemId): ?Scoreboard
    {
        $row = $this->boards()->where('i.id', $itemId)->first();

        return $row === null ? null : $this->board($row);
    }

    public function top(int $itemId, int $limit = 5): Collection
    {
        $rows = DB::table('items_highscore_data')->where('item_id', $itemId)->orderByDesc('score')->limit($limit)->get(['user_ids', 'score']);
        $ids = $rows->flatMap(fn (object $row): array => array_map(intval(...), explode(',', (string) $row->user_ids)))->unique()->values()->all();
        $names = User::query()->whereIn('id', $ids)->pluck('username', 'id');

        return $rows->map(fn (object $row): GameScore => new GameScore(
            (int) $row->score,
            array_values(array_filter(array_map(fn (string $id): string => (string) ($names[(int) $id] ?? ''), explode(',', (string) $row->user_ids)))),
        ))->values();
    }

    private function boards(): Builder
    {
        return DB::table('items as i')
            ->join('items_base as b', 'b.id', '=', 'i.item_id')
            ->join('rooms as r', 'r.id', '=', 'i.room_id')
            ->where('b.item_name', 'like', 'highscore%')
            ->select(['i.id', 'b.item_name', 'r.id as room_id', 'r.name']);
    }

    private function board(object $row): Scoreboard
    {
        /** @var object{id: int, item_name: string, room_id: int, name: string} $row */
        return new Scoreboard((int) $row->id, (string) $row->item_name, (int) $row->room_id, (string) $row->name);
    }
}

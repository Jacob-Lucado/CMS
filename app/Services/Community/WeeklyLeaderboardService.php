<?php

namespace App\Services\Community;

use App\Emulator\Data\LeaderboardEntry;
use App\Emulator\Support\LeaderboardEntries;
use App\Models\Community\WeeklyWinner;
use App\Models\RoomStreamPlay;
use App\Models\Social\Kudos;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

/**
 * Leaderboards that start over every Monday: songs played in rooms and kudos received.
 */
class WeeklyLeaderboardService
{
    public const BOARDS = ['music', 'kudos'];

    public function __construct(private readonly StaffService $staff) {}

    /** The Monday that starts the week containing the given moment. */
    public function weekStart(?Carbon $at = null): Carbon
    {
        return ($at ?? now())->copy()->startOfWeek()->startOfDay();
    }

    /**
     * @return Collection<int, LeaderboardEntry>
     */
    public function standings(string $board, Carbon $weekStart, int $limit = 9): Collection
    {
        $end = $weekStart->copy()->addWeek();
        $staffIds = $this->staff->fetchEmployeeIds();

        $values = match ($board) {
            'music' => $this->songsPlayed($weekStart, $end, $staffIds, $limit),
            'kudos' => $this->kudosReceived($weekStart, $end, $staffIds, $limit),
            default => [],
        };

        return LeaderboardEntries::forUsers($values);
    }

    /**
     * @return array{weeklyMusic: Collection<int, LeaderboardEntry>, weeklyKudos: Collection<int, LeaderboardEntry>, lastWinners: Collection<int, WeeklyWinner>}
     */
    public function current(): array
    {
        $start = $this->weekStart();

        return [
            'weeklyMusic' => $this->standings('music', $start),
            'weeklyKudos' => $this->standings('kudos', $start),
            'lastWinners' => $this->lastWinners(),
        ];
    }

    /**
     * Where a player stands on each weekly board, or null when they have no score yet this week.
     *
     * @return array{music: array{rank: int, value: int}|null, kudos: array{rank: int, value: int}|null}
     */
    public function standingFor(User $user): array
    {
        $start = $this->weekStart();
        $standing = [];

        foreach (self::BOARDS as $board) {
            $standing[$board] = null;

            foreach ($this->standings($board, $start, 500)->values() as $index => $entry) {
                if ($entry->user->id === $user->id) {
                    $standing[$board] = ['rank' => $index + 1, 'value' => $entry->value];

                    break;
                }
            }
        }

        return ['music' => $standing['music'], 'kudos' => $standing['kudos']];
    }

    /** @return Collection<int, WeeklyWinner> */
    public function lastWinners(): Collection
    {
        $latest = WeeklyWinner::query()->max('week_start');

        if ($latest === null) {
            return collect();
        }

        return WeeklyWinner::query()->with('user:id,username,look')->whereDate('week_start', $latest)->orderBy('board')->get();
    }

    /**
     * Weekly wins from the last few weeks, shown as small crowns on a profile.
     *
     * @return Collection<int, WeeklyWinner>
     */
    public function recentWins(User $user, int $weeks = 4): Collection
    {
        return WeeklyWinner::query()
            ->where('user_id', $user->id)
            ->where('week_start', '>=', $this->weekStart()->subWeeks($weeks)->toDateString())
            ->orderByDesc('week_start')
            ->orderBy('board')
            ->get();
    }

    /**
     * @param  array<int, int>  $staffIds
     *
     * @return array<int, int>
     */
    private function songsPlayed(Carbon $start, Carbon $end, array $staffIds, int $limit): array
    {
        if (! Schema::hasTable('room_stream_plays')) {
            return [];
        }

        $plays = RoomStreamPlay::query()
            ->where('played_at', '>=', $start)
            ->where('played_at', '<', $end)
            ->selectRaw('set_by, COUNT(*) as plays')
            ->groupBy('set_by')
            ->orderByDesc('plays')
            ->orderBy('set_by')
            ->limit($limit + count($staffIds))
            ->pluck('plays', 'set_by');

        $ids = User::query()->whereIn('username', $plays->keys())->whereNotIn('id', $staffIds)->pluck('id', 'username')
            ->mapWithKeys(fn (int $id, string $name): array => [mb_strtolower($name) => $id]);

        $values = [];

        foreach ($plays as $name => $count) {
            $id = $ids->get(mb_strtolower((string) $name));

            if ($id !== null && ! isset($values[$id])) {
                $values[$id] = (int) $count;
            }
        }

        return array_slice($values, 0, $limit, true);
    }

    /**
     * @param  array<int, int>  $staffIds
     *
     * @return array<int, int>
     */
    private function kudosReceived(Carbon $start, Carbon $end, array $staffIds, int $limit): array
    {
        return Kudos::query()
            ->where('given_on', '>=', $start->toDateString())
            ->where('given_on', '<', $end->toDateString())
            ->whereNotIn('to_user_id', $staffIds)
            ->selectRaw('to_user_id, COUNT(*) as total')
            ->groupBy('to_user_id')
            ->orderByDesc('total')
            ->orderBy('to_user_id')
            ->limit($limit)
            ->pluck('total', 'to_user_id')
            ->map(fn (mixed $total): int => (int) $total)
            ->all();
    }
}

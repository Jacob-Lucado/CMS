<?php

namespace App\Http\Controllers\Social;

use App\Emulator\Contracts\ScoreboardRepository;
use App\Emulator\Data\GameScore;
use App\Http\Controllers\Controller;
use App\Models\Social\Gameboard;
use App\Models\Social\Kudos;
use App\Models\User;
use App\Services\Social\SocialFeed;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class GameboardController extends Controller
{
    public function __invoke(Request $request, ScoreboardRepository $scores, SocialFeed $feed): View
    {
        $viewer = $request->user();
        abort_unless($viewer instanceof User, 401);

        /** @var Collection<int, array{board: Gameboard, top: Collection<int, GameScore>}> $boards */
        $boards = Gameboard::query()->where('enabled', true)->orderBy('order_num')->orderBy('id')->get()
            ->map(fn (Gameboard $board): array => ['board' => $board, 'top' => $scores->top($board->item_id, 5)]);

        $names = $boards->flatMap(fn (array $row): array => $row['top']->flatMap(fn (GameScore $score): array => $score->players)->all())->unique()->values()->all();
        $users = User::query()->whereIn('username', $names)->get()->keyBy(fn (User $user): string => mb_strtolower($user->username));
        $kudos = Kudos::query()->whereIn('to_user_id', $users->pluck('id')->all())->where('board', 'like', 'game_%')
            ->selectRaw('to_user_id, board, count(*) as total')->groupBy('to_user_id', 'board')->get()
            ->mapWithKeys(fn (Kudos $row): array => [$row->board . ':' . $row->to_user_id => (int) $row->getAttribute('total')]);

        return view('gameboards', [
            'viewer' => $viewer,
            'kudosUrl' => route('kudos.store', absolute: false),
            'boards' => $this->present($boards, $users, $kudos, $feed),
        ]);
    }

    /**
     * @param  Collection<int, array{board: Gameboard, top: Collection<int, GameScore>}>  $boards
     * @param  Collection<string, User>  $users
     * @param  Collection<string, int>  $kudos
     *
     * @return list<array<string, mixed>>
     */
    private function present(Collection $boards, Collection $users, Collection $kudos, SocialFeed $feed): array
    {
        $out = [];

        foreach ($boards as $row) {
            $scores = [];

            foreach ($row['top'] as $score) {
                $players = [];

                foreach ($score->players as $name) {
                    $user = $users->get(mb_strtolower($name));
                    $players[] = [
                        'name' => $name,
                        'id' => $user?->id,
                        'head' => $user ? ($feed->author($user)['head'] ?? null) : null,
                        'kudos' => $user ? (int) ($kudos['game_' . $row['board']->id . ':' . $user->id] ?? 0) : 0,
                    ];
                }

                $scores[] = ['score' => $score->score, 'players' => $players];
            }

            $out[] = ['id' => $row['board']->id, 'name' => $row['board']->name, 'description' => $row['board']->description, 'room' => $row['board']->room_id, 'scores' => $scores];
        }

        return $out;
    }
}

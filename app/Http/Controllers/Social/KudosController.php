<?php

namespace App\Http\Controllers\Social;

use App\Http\Controllers\Controller;
use App\Models\Social\Gameboard;
use App\Models\Social\Kudos;
use App\Models\User;
use App\Services\Social\Notifier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

/**
 * One kudos per player, per board, per day.
 */
class KudosController extends Controller
{
    public function __invoke(Request $request, Notifier $notifier): JsonResponse
    {
        $from = $request->user();
        abort_unless($from instanceof User, 401);

        $data = $request->validate([
            'user_id' => ['required', 'integer'],
            'board' => ['required', 'string', 'max:40', 'regex:/^[a-z0-9_-]+$/'],
        ]);

        $to = User::query()->whereKey((int) $data['user_id'])->firstOrFail();

        if ($to->id === $from->id) {
            throw ValidationException::withMessages(['user_id' => __("You can't give yourself kudos.")]);
        }

        $kudos = Kudos::query()->firstOrCreate([
            'from_user_id' => $from->id,
            'to_user_id' => $to->id,
            'board' => $data['board'],
            'given_on' => today()->toDateString(),
        ]);

        if (! $kudos->wasRecentlyCreated) {
            throw ValidationException::withMessages(['user_id' => __('You already gave :name kudos here today.', ['name' => $to->username])]);
        }

        $game = str_starts_with($data['board'], 'game_') ? Gameboard::query()->find((int) substr($data['board'], 5)) : null;
        $notifier->send(
            $to,
            'kudos',
            __(':name gave you kudos on the :board board.', ['name' => $from->username, 'board' => $game->name ?? str_replace(['_', '-'], ' ', $data['board'])]),
            $game ? route('gameboards.index', absolute: false) : route('leaderboard.index', absolute: false),
            $from,
        );

        return response()->json([
            'kudos' => Kudos::query()->where('to_user_id', $to->id)->count(),
            'message' => __('Kudos sent to :name!', ['name' => $to->username]),
        ]);
    }
}

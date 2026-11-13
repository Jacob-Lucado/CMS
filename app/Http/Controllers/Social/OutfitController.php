<?php

namespace App\Http\Controllers\Social;

use App\Http\Controllers\Controller;
use App\Models\Social\Outfit;
use App\Models\User;
use App\Services\Social\Notifier;
use App\Services\Social\OutfitOfTheWeek;
use App\Services\Social\SocialFeed;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OutfitController extends Controller
{
    public function __construct(private readonly OutfitOfTheWeek $weeks, private readonly SocialFeed $feed, private readonly Notifier $notifier) {}

    public function show(Request $request): View
    {
        $viewer = $this->viewer($request);
        $this->weeks->settle();
        $week = $this->weeks->currentWeek();

        $entries = Outfit::query()->with('user')->whereDate('week', $week->toDateString())->orderByDesc('votes')->orderBy('id')->limit(100)->get();
        $voted = DB::table('website_outfit_votes')->where('user_id', $viewer->id)->whereIn('outfit_id', $entries->modelKeys())->pluck('outfit_id')->all();
        $winners = Outfit::query()->with('user')->where('winner', true)->latest('week')->limit(8)->get();

        return view('outfits', [
            'config' => [
                'imager' => (string) setting('avatar_imager'),
                'me' => [...($this->feed->author($viewer) ?? []), 'look' => (string) $viewer->look],
                'submit' => route('outfits.store', absolute: false),
                'base' => rtrim(route('outfits.index', absolute: false), '/'),
                'post' => route('social.posts.store', absolute: false),
                'ends' => $week->addWeek()->toIso8601String(),
                'entries' => $entries->map(fn (Outfit $outfit): array => $this->entry($outfit, $viewer, in_array($outfit->id, $voted, true)))->values(),
                'winners' => $winners->map(fn (Outfit $outfit): array => [...$this->entry($outfit, $viewer, false), 'week' => $outfit->week->isoFormat('MMM D, YYYY')])->values(),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $viewer = $this->viewer($request);
        abort_if(blank($viewer->look), 422, __('Put on an outfit in the hotel first.'));

        $outfit = Outfit::query()->updateOrCreate(
            ['user_id' => $viewer->id, 'week' => $this->weeks->currentWeek()->toDateString()],
            ['look' => (string) $viewer->look, 'gender' => (string) ($viewer->gender ?: 'M')],
        );

        return response()->json(['entry' => $this->entry($outfit->load('user'), $viewer, false)], $outfit->wasRecentlyCreated ? 201 : 200);
    }

    public function vote(Request $request, Outfit $outfit): JsonResponse
    {
        $viewer = $this->viewer($request);

        if ($outfit->week->toDateString() !== $this->weeks->currentWeek()->toDateString()) {
            throw ValidationException::withMessages(['outfit' => __('Voting for that week is closed.')]);
        }

        if ($outfit->user_id === $viewer->id) {
            throw ValidationException::withMessages(['outfit' => __("You can't vote for your own outfit.")]);
        }

        $key = ['outfit_id' => $outfit->id, 'user_id' => $viewer->id];
        $voted = DB::transaction(function () use ($key, $outfit): bool {
            if (DB::table('website_outfit_votes')->where($key)->delete() > 0) {
                $outfit->decrement('votes');

                return false;
            }

            DB::table('website_outfit_votes')->insert([...$key, 'created_at' => now()]);
            $outfit->increment('votes');

            return true;
        });

        if ($voted) {
            $this->notifier->send($outfit->user_id, 'outfit_like', __(':name voted for your Outfit of the Week entry.', ['name' => $viewer->username]), route('outfits.index', absolute: false), $viewer);
        }

        return response()->json(['voted' => $voted, 'votes' => $outfit->fresh()->votes ?? 0]);
    }

    /** @return array<string, mixed> */
    private function entry(Outfit $outfit, User $viewer, bool $voted): array
    {
        return [
            'id' => $outfit->id,
            'look' => $outfit->look,
            'votes' => $outfit->votes,
            'voted' => $voted,
            'mine' => $outfit->user_id === $viewer->id,
            'author' => $this->feed->author($outfit->user),
        ];
    }

    private function viewer(Request $request): User
    {
        $user = $request->user();
        abort_unless($user instanceof User, 401);

        return $user;
    }
}

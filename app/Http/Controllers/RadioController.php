<?php

namespace App\Http\Controllers;

use App\Models\Radio\RadioEntry;
use App\Models\User;
use App\Services\Radio\RadioAccess;
use App\Services\Radio\RadioSources;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class RadioController extends Controller
{
    private const MAX_ENTRIES = 300;

    public function __construct(private readonly RadioAccess $access) {}

    public function show(): View
    {
        return view('radio');
    }

    public function feed(Request $request): JsonResponse
    {
        $user = $this->user($request);
        $dj = $this->access->isDj($user);

        return response()->json([
            'dj' => $dj,
            'submissions' => $this->access->submissionsOpen(),
            'on_air' => $this->access->onAir()?->load('user')->toPlayer($user),
            'entries' => RadioEntry::query()->approved()->with('user')->orderBy('kind')->orderBy('title')->limit(self::MAX_ENTRIES)->get()
                ->map(fn (RadioEntry $entry) => $entry->toPlayer($user)),
            'pending' => $dj ? RadioEntry::query()->pending()->with('user')->oldest()->limit(50)->get()->map(fn (RadioEntry $entry) => $entry->toPlayer($user)) : [],
            'mine' => RadioEntry::query()->where('user_id', $user->id)->where('status', '!=', RadioEntry::APPROVED)->latest()->limit(10)->get()
                ->map(fn (RadioEntry $entry) => $entry->toPlayer($user)),
        ])->header('Cache-Control', 'no-store');
    }

    public function store(Request $request, RadioSources $sources): JsonResponse
    {
        $user = $this->user($request);
        $dj = $this->access->isDj($user);
        $data = $request->validate([
            'link' => ['required', 'string', 'max:300'],
            'name' => ['nullable', 'string', 'max:60'],
            'genre' => ['nullable', 'string', 'max:40'],
        ]);

        abort_unless($dj || $this->access->submissionsOpen(), 403, __('Radio submissions are closed right now.'));

        if (! $dj && RadioEntry::query()->pending()->where('user_id', $user->id)->count() >= RadioAccess::MAX_PENDING_PER_PLAYER) {
            throw ValidationException::withMessages(['link' => __('You already have :count waiting for a DJ. Hang tight.', ['count' => RadioAccess::MAX_PENDING_PER_PLAYER])]);
        }

        if ($dj && RadioEntry::query()->approved()->count() >= self::MAX_ENTRIES) {
            throw ValidationException::withMessages(['link' => __('The radio is full. Remove a few first.')]);
        }

        $attributes = $sources->fromLink($data['link'], (string) ($data['name'] ?? ''), (string) ($data['genre'] ?? ''));

        if (RadioEntry::query()->where('stream', $attributes['stream'])->exists()) {
            throw ValidationException::withMessages(['link' => __("That one's already on the radio or waiting for a DJ.")]);
        }

        $entry = RadioEntry::query()->create([
            ...$attributes,
            'status' => $dj ? RadioEntry::APPROVED : RadioEntry::PENDING,
            'user_id' => $user->id,
            'reviewed_by' => $dj ? $user->id : null,
        ]);

        return response()->json([
            'entry' => $entry->load('user')->toPlayer($user),
            'message' => $dj ? __('Added to the radio.') : __('Thanks! A DJ will take a listen.'),
        ], 201);
    }

    public function approve(Request $request, RadioEntry $entry): JsonResponse
    {
        return $this->review($request, $entry, RadioEntry::APPROVED);
    }

    public function reject(Request $request, RadioEntry $entry): JsonResponse
    {
        return $this->review($request, $entry, RadioEntry::REJECTED);
    }

    public function destroy(Request $request, RadioEntry $entry): JsonResponse
    {
        $user = $this->user($request);
        $withdrawing = $entry->user_id === $user->id && $entry->status !== RadioEntry::APPROVED;

        abort_unless($withdrawing || $this->access->isDj($user), 403);

        if ($this->access->onAir()?->is($entry)) {
            $this->access->putOnAir(null);
        }

        $entry->delete();

        return response()->json(['ok' => true]);
    }

    public function onAir(Request $request): JsonResponse
    {
        abort_unless($this->access->isDj($this->user($request)), 403);
        $id = $request->validate(['id' => ['nullable', 'integer']])['id'] ?? null;

        $this->access->putOnAir($id === null ? null : RadioEntry::query()->approved()->findOrFail((int) $id));

        return response()->json(['ok' => true]);
    }

    private function review(Request $request, RadioEntry $entry, string $status): JsonResponse
    {
        $user = $this->user($request);
        abort_unless($this->access->isDj($user), 403);

        $entry->forceFill(['status' => $status, 'reviewed_by' => $user->id])->save();

        return response()->json(['ok' => true]);
    }

    private function user(Request $request): User
    {
        $user = $request->user();
        abort_unless($user instanceof User, 403);

        return $user;
    }
}

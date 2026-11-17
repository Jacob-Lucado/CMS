<?php

namespace App\Http\Controllers\Community;

use App\Http\Controllers\Controller;
use App\Models\WebsiteEvent;
use App\Services\Community\DailyRewardService;
use App\Services\Community\RiddleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Feeds the in-game Daily Hub window: the daily reward, the riddle of the day and the next event.
 */
class HubController extends Controller
{
    public function show(Request $request, DailyRewardService $rewards, RiddleService $riddles): JsonResponse
    {
        $event = WebsiteEvent::query()->where('published', true)->where('starts_at', '>=', now())->orderBy('starts_at')->first();

        return response()->json([
            'daily' => $rewards->status($request->user()),
            'riddle' => $riddles->status($request->user()),
            'event' => $event ? ['title' => $event->title, 'location' => $event->location, 'starts_at' => $event->starts_at->toIso8601String()] : null,
        ]);
    }

    public function claim(Request $request, DailyRewardService $rewards): JsonResponse
    {
        $result = $rewards->claim($request->user());

        return response()->json($result + ['daily' => $rewards->status($request->user())], $result['ok'] ? 200 : 422);
    }

    public function answer(Request $request, RiddleService $riddles): JsonResponse
    {
        $data = $request->validate(['answer' => ['required', 'string', 'max:100']]);
        $result = $riddles->answer($request->user(), $data['answer']);

        return response()->json($result + ['riddle' => $riddles->status($request->user())], $result['ok'] ? 200 : 422);
    }
}

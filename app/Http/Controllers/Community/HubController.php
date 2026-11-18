<?php

namespace App\Http\Controllers\Community;

use App\Http\Controllers\Controller;
use App\Models\ChangelogEntry;
use App\Models\Community\RareValue\WebsiteRareValue;
use App\Models\WebsiteEvent;
use App\Models\WebsitePoll;
use App\Models\WebsitePollVote;
use App\Models\User;
use App\Services\Community\DailyRewardService;
use App\Services\Community\RiddleService;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Feeds the in-game Daily Hub window: the daily reward, the riddle of the day, the open poll, the next event and the latest news.
 */
class HubController extends Controller
{
    public function show(Request $request, DailyRewardService $rewards, RiddleService $riddles): JsonResponse
    {
        $event = WebsiteEvent::query()->where('published', true)->where('starts_at', '>=', now())->orderBy('starts_at')->first();

        $news = ChangelogEntry::query()->where('published', true)->latest('published_at')->first();

        return response()->json([
            'news' => $news ? ['title' => $news->title, 'category' => $news->category, 'body' => \Illuminate\Support\Str::limit(strip_tags($news->body), 220), 'published_at' => $news->published_at?->toIso8601String()] : null,
            'daily' => $rewards->status($request->user()),
            'riddle' => $riddles->status($request->user()),
            'poll' => $this->pollStatus($request->user()),
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

    public function vote(Request $request): JsonResponse
    {
        $data = $request->validate(['option' => ['required', 'integer', 'min:0']]);
        $poll = $this->openPoll();

        if (! $poll || ! isset($poll->choices()[$data['option']])) {
            return response()->json(['ok' => false, 'message' => __('There is no poll to vote in right now.'), 'poll' => $this->pollStatus($request->user())], 422);
        }

        try {
            WebsitePollVote::query()->create(['poll_id' => $poll->id, 'user_id' => $request->user()->id, 'option_index' => $data['option']]);
        } catch (QueryException) {
            return response()->json(['ok' => false, 'message' => __('You already voted in this poll.'), 'poll' => $this->pollStatus($request->user())], 422);
        }

        return response()->json(['ok' => true, 'message' => __('Thanks for voting.'), 'poll' => $this->pollStatus($request->user())]);
    }

    public function values(Request $request): JsonResponse
    {
        $term = trim((string) $request->query('q', ''));

        if (mb_strlen($term) < 2) {
            return response()->json(['results' => []]);
        }

        $results = WebsiteRareValue::query()
            ->where('name', 'like', '%' . \App\Support\Sql::escapeLike($term) . '%')
            ->orderBy('name')->limit(8)->get()
            ->map(fn (WebsiteRareValue $v) => [
                'name' => $v->name,
                'credits' => $v->credit_value,
                'other' => $v->currency_value,
                'other_label' => $v->currency_type === 0 ? 'Duckets' : ($v->currency_type === 5 ? 'Diamonds' : null),
            ])->values();

        return response()->json(['results' => $results]);
    }

    private function openPoll(): ?WebsitePoll
    {
        return WebsitePoll::query()->where('published', true)
            ->where(fn ($q) => $q->whereNull('closes_at')->orWhere('closes_at', '>', now()))
            ->latest('id')->first();
    }

    /** @return array<string, mixed>|null */
    private function pollStatus(User $user): ?array
    {
        $poll = $this->openPoll();

        if (! $poll) {
            return null;
        }

        $counts = WebsitePollVote::query()->where('poll_id', $poll->id)
            ->selectRaw('option_index, count(*) as total')->groupBy('option_index')->pluck('total', 'option_index');
        $mine = WebsitePollVote::query()->where('poll_id', $poll->id)->where('user_id', $user->id)->value('option_index');

        return [
            'question' => $poll->question,
            'choices' => array_map(fn (string $label, int $i) => ['label' => $label, 'votes' => (int) ($counts[$i] ?? 0)], $poll->choices(), array_keys($poll->choices())),
            'voted' => $mine !== null ? (int) $mine : null,
        ];
    }
}

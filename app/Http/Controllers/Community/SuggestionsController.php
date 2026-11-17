<?php

namespace App\Http\Controllers\Community;

use App\Http\Controllers\Controller;
use App\Models\Suggestion;
use App\Models\SuggestionVote;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SuggestionsController extends Controller
{
    public function index(Request $request): View
    {
        $status = in_array($request->query('status'), Suggestion::STATUSES, true) ? $request->query('status') : null;

        $suggestions = Suggestion::query()
            ->with('author:id,username')
            ->withCount('votes')
            ->when($status, fn ($query) => $query->where('status', $status), fn ($query) => $query->whereNot('status', 'declined'))
            ->orderByDesc('votes_count')->latest('id')
            ->paginate(15)->withQueryString();

        return view('suggestions', [
            'suggestions' => $suggestions,
            'status' => $status,
            'voted' => $request->user()
                ? SuggestionVote::query()->where('user_id', $request->user()->id)->whereIn('suggestion_id', $suggestions->pluck('id'))->pluck('suggestion_id')->all()
                : [],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'min:5', 'max:120'],
            'body' => ['required', 'string', 'min:15', 'max:1500'],
        ]);

        Suggestion::query()->create($data + ['user_id' => $request->user()->id]);

        return back()->with('status', __('Thanks! Your idea is on the board.'));
    }

    public function vote(Request $request, Suggestion $suggestion): RedirectResponse
    {
        if ($suggestion->user_id === $request->user()->id) {
            return back()->with('status', __('You cannot vote for your own idea.'));
        }

        try {
            SuggestionVote::query()->create(['suggestion_id' => $suggestion->id, 'user_id' => $request->user()->id]);
        } catch (QueryException) {
            SuggestionVote::query()->where('suggestion_id', $suggestion->id)->where('user_id', $request->user()->id)->delete();
        }

        return back();
    }
}

<?php

namespace App\Http\Controllers\Community;

use App\Http\Controllers\Controller;
use App\Models\WebsitePoll;
use App\Models\WebsitePollVote;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PollsController extends Controller
{
    public function index(Request $request): View
    {
        $polls = WebsitePoll::query()->where('published', true)->latest('id')->limit(10)->get();
        $counts = WebsitePollVote::query()
            ->whereIn('poll_id', $polls->pluck('id'))
            ->selectRaw('poll_id, option_index, count(*) as total')
            ->groupBy('poll_id', 'option_index')->get()
            ->groupBy('poll_id');

        $mine = $request->user()
            ? WebsitePollVote::query()->where('user_id', $request->user()->id)->whereIn('poll_id', $polls->pluck('id'))->pluck('option_index', 'poll_id')
            : collect();

        return view('polls', ['polls' => $polls, 'counts' => $counts, 'mine' => $mine]);
    }

    public function vote(Request $request, WebsitePoll $poll): RedirectResponse
    {
        $data = $request->validate(['option' => ['required', 'integer', 'min:0']]);

        if (! $poll->isOpen() || ! isset($poll->choices()[$data['option']])) {
            return back()->with('status', __('This poll is closed.'));
        }

        try {
            WebsitePollVote::query()->create(['poll_id' => $poll->id, 'user_id' => $request->user()->id, 'option_index' => $data['option']]);
        } catch (QueryException) {
            return back()->with('status', __('You already voted in this poll.'));
        }

        return back()->with('status', __('Thanks for voting.'));
    }
}

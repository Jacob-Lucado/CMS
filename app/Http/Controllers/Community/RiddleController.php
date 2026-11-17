<?php

namespace App\Http\Controllers\Community;

use App\Actions\SendCurrency;
use App\Enums\CurrencyTypes;
use App\Exceptions\CurrencyGrantException;
use App\Http\Controllers\Controller;
use App\Models\WebsiteRiddle;
use App\Models\WebsiteRiddleSolve;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RiddleController extends Controller
{
    public function show(Request $request): View
    {
        $riddle = WebsiteRiddle::today();

        return view('riddle', [
            'riddle' => $riddle,
            'solved' => $riddle && $request->user() && $riddle->solves()->where('user_id', $request->user()->id)->exists(),
            'solvers' => $riddle?->solves()->count() ?? 0,
        ]);
    }

    public function answer(Request $request, SendCurrency $currency): RedirectResponse
    {
        $data = $request->validate(['answer' => ['required', 'string', 'max:100']]);
        $riddle = WebsiteRiddle::today();
        $user = $request->user();

        if ($riddle === null) {
            return back()->with('status', __('There is no riddle today.'));
        }

        if ($riddle->solves()->where('user_id', $user->id)->exists()) {
            return back()->with('status', __('You already solved today\'s riddle.'));
        }

        if (! $riddle->accepts($data['answer'])) {
            return back()->with('status', __('Not quite. Try again.'))->withInput();
        }

        try {
            $currency->execute($user, CurrencyTypes::Credits, $riddle->reward_credits);
        } catch (CurrencyGrantException) {
            return back()->with('status', __('Correct, but we could not send your reward while you are in the hotel. Leave the hotel and answer again.'));
        }

        WebsiteRiddleSolve::query()->firstOrCreate(['riddle_id' => $riddle->id, 'user_id' => $user->id]);

        return back()->with('status', __('Correct! You got :amount credits.', ['amount' => $riddle->reward_credits]));
    }
}

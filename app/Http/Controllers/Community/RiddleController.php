<?php

namespace App\Http\Controllers\Community;

use App\Http\Controllers\Controller;
use App\Services\Community\RiddleService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RiddleController extends Controller
{
    public function show(Request $request, RiddleService $riddles): View
    {
        $status = $riddles->status($request->user());

        return view('riddle', [
            'riddle' => $status === null ? null : (object) ['question' => $status['question'], 'hint' => $status['hint'], 'reward_credits' => $status['reward']],
            'solved' => $status['solved'] ?? false,
            'solvers' => $status['solvers'] ?? 0,
        ]);
    }

    public function answer(Request $request, RiddleService $riddles): RedirectResponse
    {
        $data = $request->validate(['answer' => ['required', 'string', 'max:100']]);
        $result = $riddles->answer($request->user(), $data['answer']);

        $redirect = back()->with('status', $result['message']);

        return $result['ok'] ? $redirect : $redirect->withInput();
    }
}

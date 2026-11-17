<?php

namespace App\Services\Community;

use App\Actions\SendCurrency;
use App\Enums\CurrencyTypes;
use App\Exceptions\CurrencyGrantException;
use App\Models\User;
use App\Models\WebsiteRiddle;
use App\Models\WebsiteRiddleSolve;

class RiddleService
{
    public function __construct(private readonly SendCurrency $currency) {}

    /** @return array{question: string, hint: string|null, reward: int, solved: bool, solvers: int}|null */
    public function status(?User $user): ?array
    {
        $riddle = WebsiteRiddle::today();

        if ($riddle === null) {
            return null;
        }

        return [
            'question' => $riddle->question,
            'hint' => $riddle->hint,
            'reward' => $riddle->reward_credits,
            'solved' => $user !== null && $riddle->solves()->where('user_id', $user->id)->exists(),
            'solvers' => $riddle->solves()->count(),
        ];
    }

    /** @return array{ok: bool, message: string} */
    public function answer(User $user, string $guess): array
    {
        $riddle = WebsiteRiddle::today();

        if ($riddle === null) {
            return ['ok' => false, 'message' => __('There is no riddle today.')];
        }

        if ($riddle->solves()->where('user_id', $user->id)->exists()) {
            return ['ok' => false, 'message' => __('You already solved today\'s riddle.')];
        }

        if (! $riddle->accepts($guess)) {
            return ['ok' => false, 'message' => __('Not quite. Try again.')];
        }

        try {
            $this->currency->execute($user, CurrencyTypes::Credits, $riddle->reward_credits);
        } catch (CurrencyGrantException) {
            return ['ok' => false, 'message' => __('Correct, but we could not send your reward while you are in the hotel. Leave the hotel and answer again.')];
        }

        WebsiteRiddleSolve::query()->firstOrCreate(['riddle_id' => $riddle->id, 'user_id' => $user->id]);

        return ['ok' => true, 'message' => __('Correct! You got :amount credits.', ['amount' => $riddle->reward_credits])];
    }
}

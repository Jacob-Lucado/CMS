<?php

namespace App\Services\Social;

use App\Models\Social\Outfit;
use App\Models\Social\SocialPost;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;

/**
 * Weeks start on Monday. When a week ends, its entry with the most votes wins; the earlier entry wins a tie.
 * A week nobody voted in has no winner.
 */
class OutfitOfTheWeek
{
    public function __construct(private readonly Notifier $notifier) {}

    public function currentWeek(): CarbonImmutable
    {
        return CarbonImmutable::now()->startOfWeek(CarbonImmutable::MONDAY)->startOfDay();
    }

    /** Picks winners for every finished week that has none yet. */
    public function settle(): void
    {
        $weeks = Outfit::query()
            ->where('week', '<', $this->currentWeek()->toDateString())
            ->select('week')
            ->groupBy('week')
            ->havingRaw('MAX(winner) = 0')
            ->pluck('week');

        foreach ($weeks as $week) {
            DB::transaction(function () use ($week): void {
                $winner = Outfit::query()->whereDate('week', $week)->where('votes', '>', 0)->orderByDesc('votes')->orderBy('id')->lockForUpdate()->first();

                if ($winner === null) {
                    return;
                }

                $winner->forceFill(['winner' => true])->save();
                $this->notifier->send($winner->user_id, 'outfit_like', __('Your outfit won Outfit of the Week with :votes votes!', ['votes' => $winner->votes]), route('outfits.index', absolute: false));
                SocialPost::query()->create([
                    'user_id' => $winner->user_id,
                    'type' => SocialPost::OUTFIT,
                    'body' => __('Won Outfit of the Week with :votes votes! 🏆', ['votes' => $winner->votes]),
                    'data' => ['look' => $winner->look, 'gender' => $winner->gender, 'outfit_id' => $winner->id, 'winner' => true],
                ]);
            });
        }
    }
}

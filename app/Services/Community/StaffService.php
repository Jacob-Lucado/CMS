<?php

namespace App\Services\Community;

use App\Emulator\Contracts\RankRepository;
use App\Emulator\Models\Rank;
use App\Models\User;
use App\Support\CommunityCache;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;

class StaffService
{
    public function __construct(private readonly RankRepository $ranks) {}

    /** @return Collection<int, covariant Rank> */
    public function fetchStaffPositions(User $viewer): Collection
    {
        $includeHidden = $viewer->rank >= (int) setting('min_rank_to_see_hidden_staff');
        $resolve = fn (): Collection => $this->ranks->staffPositions($includeHidden);

        if (setting('enable_caching') !== '1') {
            return $resolve();
        }

        return Cache::remember(
            CommunityCache::staffPositionsKey($includeHidden),
            now()->addMinutes((int) setting('cache_timer')),
            $resolve,
        );
    }

    /** @return list<int> */
    public function fetchEmployeeIds(): array
    {
        $cacheEnabled = setting('enable_caching') === '1';

        $resolve = fn (): array => User::select('id')
            ->where('rank', '>=', setting('min_staff_rank'))
            ->get()
            ->pluck('id')
            ->map(fn (mixed $id): int => (int) $id)
            ->values()
            ->all();

        $ids = $cacheEnabled ? Cache::remember(
            CommunityCache::STAFF_IDS,
            now()->addMinutes((int) setting('cache_timer')),
            $resolve,
        ) : $resolve();

        return array_values($ids);
    }
}

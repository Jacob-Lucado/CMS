<div class="flex flex-col gap-2 p-2">
    <div class="flex items-start gap-3 border-b border-gray-600 pb-3">
        <div class="flex flex-col">
            <a class="font-bold text-blue-400 hover:underline" href="{{ route('home.show', $user) }}">
                {{ $user->username }}
            </a>

            @if($user->online)
                <span class="text-xs text-green-400 font-semibold">{{ __('Online') }}</span>
            @else
                <span class="text-xs text-gray-400">{{ __('Offline') }}</span>
            @endif

            <span class="text-xs text-gray-400 mt-1">
                {{ __('Member since') }}
                {{ date('Y-m-d', $user->account_created) }}
            </span>
        </div>

        <img
            class="w-16 h-auto ml-auto"
            style="image-rendering: pixelated;"
            src="{{ setting('avatar_imager') }}{{ $user->look }}&direction=4&head_direction=4&size=m&gesture=sml"
            alt="{{ $user->username }}"
        >
    </div>

    @php($drawnBadges = \App\Models\WebsiteDrawBadge::query()->where('user_id', $user->id)->where('published', true)->count())
    @if ($drawnBadges > 0)
        <div class="flex flex-wrap gap-1">
            <a href="{{ route('help-center.badges.index') }}" class="rounded-full bg-purple-100 px-2 py-0.5 text-[11px] font-semibold text-purple-800 dark:bg-purple-900 dark:text-purple-200">
                {{ trans_choice('Badge artist - :count badge|Badge artist - :count badges', $drawnBadges) }}
            </a>
        </div>
    @endif

    @php($wins = app(\App\Services\Community\WeeklyLeaderboardService::class)->recentWins($user))
    @if ($wins->isNotEmpty())
        <div class="flex flex-wrap gap-1">
            @foreach ($wins as $win)
                <span class="rounded-full bg-yellow-100 px-2 py-0.5 text-[11px] font-semibold text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200" title="{{ __('Week of :date', ['date' => $win->week_start->format('M j')]) }}">
                    &#9812; {{ $win->board === 'music' ? __('Top DJ') : __('Most kudos') }}
                </span>
            @endforeach
        </div>
    @endif

    <p class="text-xs italic text-gray-300">{{ $user->motto }}</p>
</div>

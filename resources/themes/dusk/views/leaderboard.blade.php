<x-app-layout>
    @push('title', __('Leaderboard'))

    <div class="col-span-12">
        <x-page-header>
            <x-slot:icon>
                <img src="{{ asset('/assets/images/dusk/leaderboard_icon.png') }}" alt="">
            </x-slot:icon>

            {{ __('Leaderboards') }}
        </x-page-header>

        <div class="grid grid-cols-1 gap-5 md:grid-cols-3 mt-4">
            <x-leaderboard-card title="{{ __('Top credits') }}" icon="credits.png" :data="$credits" valueType="Credits" />
            <x-leaderboard-card title="{{ __('Top duckets') }}" icon="duckets.png" :data="$duckets" valueType="Duckets" />
            <x-leaderboard-card title="{{ __('Top diamonds') }}" icon="diamond.png" :data="$diamonds" valueType="Diamonds" />
            @if ($mostOnline !== null)
                <x-leaderboard-card title="{{ __('Hours online') }}" icon="clock.gif" :data="$mostOnline" valueType="Hours online" :formatValue="fn($value) => round($value / 3600)" />
            @endif
            <x-leaderboard-card title="{{ __('Respects received') }}" icon="heart.gif" :data="$respectsReceived" valueType="Respect received" />
            <x-leaderboard-card title="{{ __('Achievement score') }}" icon="star.gif" :data="$achievementScores" valueType="Achievement points" />
        </div>

        @if ($weeklyMusic->isNotEmpty() || $weeklyKudos->isNotEmpty())
            <p class="mt-6 mb-2 text-sm font-semibold uppercase text-gray-500 dark:text-gray-400">{{ __('This week') }}</p>
            <div class="grid grid-cols-1 gap-5 md:grid-cols-3">
                @if ($weeklyMusic->isNotEmpty())
                    <x-leaderboard-card title="{{ __('Top DJs this week') }}" icon="chat.png" :data="$weeklyMusic" valueType="Songs played" />
                @endif
                @if ($weeklyKudos->isNotEmpty())
                    <x-leaderboard-card title="{{ __('Most kudos this week') }}" icon="friends.png" :data="$weeklyKudos" valueType="Kudos" />
                @endif
                @if ($lastWinners->isNotEmpty())
                    <div class="rounded bg-white p-2 shadow-sm dark:bg-gray-900">
                        <p class="text-center font-semibold text-gray-700 dark:text-gray-300">{{ __('Last week\'s winners') }}</p>
                        <hr class="dark:border-gray-500">
                        <div class="mt-4 flex flex-col gap-y-3">
                            @foreach ($lastWinners as $winner)
                                <div class="rounded bg-gray-100 p-3 dark:bg-gray-800">
                                    <p class="font-bold text-gray-700 dark:text-gray-100">{{ $winner->user->username }}</p>
                                    <p class="text-gray-600 dark:text-gray-300">
                                        {{ $winner->board === 'music' ? __('Top DJ') : __('Most kudos') }} · {{ $winner->score }}
                                    </p>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        @endif
    </div>
</x-app-layout>

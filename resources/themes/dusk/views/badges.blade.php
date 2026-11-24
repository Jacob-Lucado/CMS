<x-app-layout>
    @push('title', __('Badges'))

    <div class="col-span-12 flex flex-col gap-y-3">
        <x-content.content-card icon="hotel-icon" classes="border dark:border-gray-900">
            <x-slot:title>{{ __('Badges') }}</x-slot:title>
            <x-slot:under-title>{{ __('Every badge in the hotel. Search by name, code or what it is for.') }}</x-slot:under-title>

            <form method="GET" action="{{ route('help-center.badges.index') }}" class="mb-4 flex gap-2 px-2">
                <input type="search" name="q" value="{{ $search }}" placeholder="{{ __('Search badges') }}" class="w-full rounded border-4 border-gray-200 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 focus:ring-0 focus:border-[#eeb425]">
                <button type="submit" class="rounded border-2 border-green-500 bg-green-600 px-4 font-semibold text-white hover:bg-green-700">{{ __('Search') }}</button>
            </form>

            @if ($drawnBadges->isNotEmpty())
                <p class="mb-2 px-2 text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">{{ __('Drawn by players') }}</p>
                <div class="mb-4 grid grid-cols-1 gap-2 px-2 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($drawnBadges as $drawn)
                        <div class="flex items-center gap-3 rounded bg-gray-100 p-3 dark:bg-gray-700">
                            <div class="flex h-12 w-12 flex-none items-center justify-center rounded bg-white dark:bg-gray-800">
                                <img src="{{ $drawn->badge_url }}" alt="" loading="lazy" class="max-h-11 max-w-11" style="image-rendering: pixelated" onerror="this.style.visibility='hidden'">
                            </div>
                            <div class="min-w-0">
                                <p class="truncate text-sm font-semibold dark:text-gray-100">{{ $drawn->badge_name }}</p>
                                <p class="line-clamp-2 text-xs text-gray-600 dark:text-gray-300">{{ $drawn->badge_desc }}</p>
                                <p class="text-[11px] text-gray-400">{{ __('by :name', ['name' => $drawn->user?->username ?? __('a player')]) }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

            @if ($badges->isEmpty())
                <p class="px-2 text-sm text-gray-500 dark:text-gray-400">{{ __('No badges match that search.') }}</p>
            @else
                <div class="grid grid-cols-1 gap-2 px-2 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($badges as $badge)
                        <div class="flex items-center gap-3 rounded bg-gray-100 p-3 dark:bg-gray-700">
                            <div class="flex h-12 w-12 flex-none items-center justify-center rounded bg-white dark:bg-gray-800">
                                <img src="{{ setting('badges_path') }}{{ $badge->badge_key }}.gif" alt="" loading="lazy" class="max-h-11 max-w-11" style="image-rendering: pixelated" onerror="this.style.visibility='hidden'">
                            </div>
                            <div class="min-w-0">
                                <p class="truncate text-sm font-semibold dark:text-gray-100">{{ $badge->badge_name }}</p>
                                <p class="line-clamp-2 text-xs text-gray-600 dark:text-gray-300">{{ $badge->badge_description }}</p>
                                <p class="text-[11px] text-gray-400">{{ $badge->badge_key }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-4 px-2">{{ $badges->links() }}</div>
            @endif
        </x-content.content-card>
    </div>
</x-app-layout>

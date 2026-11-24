<x-app-layout>
    @push('title', __('Music charts'))

    <div class="col-span-12">
        <x-page-header>
            <x-slot:icon>
                <img src="{{ asset('/assets/images/dusk/leaderboard_icon.png') }}" alt="">
            </x-slot:icon>

            {{ __('Music charts') }}
        </x-page-header>

        <div class="grid grid-cols-1 gap-5 md:grid-cols-3 mt-4">
            @if (!$available || (count($songs) === 0 && count($recent) === 0))
                <div class="rounded bg-white p-4 text-center text-gray-600 shadow-sm md:col-span-3 dark:bg-gray-900 dark:text-gray-300">
                    {{ __('No songs have been played yet. Start one in your room with :music and a link.') }}
                </div>
            @else
                <div class="rounded bg-white p-2 shadow-sm dark:bg-gray-900">
                    <div class="text-center font-semibold text-gray-700 dark:text-gray-300">{{ __('Top songs this week') }}</div>
                    <hr class="dark:border-gray-500">
                    <div class="mt-4 flex flex-col gap-y-3">
                        @forelse ($songs as $index => $song)
                            <div class="flex items-center gap-x-3 rounded bg-gray-100 p-3 dark:bg-gray-800">
                                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-gray-300">{{ $index + 1 }}</div>
                                <div class="min-w-0 flex-1">
                                    @if (str_starts_with($song->url, 'https://'))
                                        <a href="{{ $song->url }}" target="_blank" rel="noopener noreferrer" class="block truncate font-bold text-gray-700 hover:underline dark:text-gray-100">{{ \Illuminate\Support\Str::limit(preg_replace('#^https://(www\.)?#', '', $song->url), 48) }}</a>
                                    @else
                                        <p class="truncate font-bold text-gray-700 dark:text-gray-100">{{ \Illuminate\Support\Str::limit($song->url, 48) }}</p>
                                    @endif
                                    <p class="text-sm text-gray-600 dark:text-gray-300">{{ ucfirst($song->kind) }} - {{ trans_choice(':count play|:count plays', (int) $song->plays) }}</p>
                                </div>
                            </div>
                        @empty
                            <p class="p-3 text-center text-gray-600 dark:text-gray-300">{{ __('Nothing this week yet.') }}</p>
                        @endforelse
                    </div>
                </div>

                <div class="rounded bg-white p-2 shadow-sm dark:bg-gray-900">
                    <div class="text-center font-semibold text-gray-700 dark:text-gray-300">{{ __('Top DJs this week') }}</div>
                    <hr class="dark:border-gray-500">
                    <div class="mt-4 flex flex-col gap-y-3">
                        @forelse ($djs as $index => $dj)
                            <div class="flex items-center gap-x-3 rounded bg-gray-100 p-3 dark:bg-gray-800">
                                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-gray-300">{{ $index + 1 }}</div>
                                <div class="min-w-0 flex-1">
                                    <p class="truncate font-bold text-gray-700 dark:text-gray-100">{{ $dj->set_by }}</p>
                                    <p class="text-sm text-gray-600 dark:text-gray-300">{{ trans_choice(':count song|:count songs', (int) $dj->plays) }}</p>
                                </div>
                            </div>
                        @empty
                            <p class="p-3 text-center text-gray-600 dark:text-gray-300">{{ __('Nothing this week yet.') }}</p>
                        @endforelse
                    </div>
                </div>

                <div class="rounded bg-white p-2 shadow-sm dark:bg-gray-900">
                    <div class="text-center font-semibold text-gray-700 dark:text-gray-300">{{ __('Just played') }}</div>
                    <hr class="dark:border-gray-500">
                    <div class="mt-4 flex flex-col gap-y-3">
                        @foreach ($recent as $play)
                            <div class="rounded bg-gray-100 p-3 dark:bg-gray-800">
                                <p class="truncate font-bold text-gray-700 dark:text-gray-100">{{ \Illuminate\Support\Str::limit(preg_replace('#^https?://(www\.)?#', '', $play->url), 48) }}</p>
                                <p class="text-sm text-gray-600 dark:text-gray-300">{{ $play->set_by }}@if ($play->room_name) {{ __('in') }} <a href="{{ route('music.room', $play->room_id) }}" class="hover:underline">{{ $play->room_name }}</a>@endif - {{ $play->played_at->diffForHumans() }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>

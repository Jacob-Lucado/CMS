<x-app-layout>
    @push('title', __('Rooms'))

    <div class="col-span-12 flex flex-col gap-y-3">
        <x-content.content-card icon="hotel-icon" classes="border dark:border-gray-900">
            <x-slot:title>{{ __('Room browser') }}</x-slot:title>
            <x-slot:under-title>{{ __('Open rooms in the hotel. Search by room name or owner.') }}</x-slot:under-title>

            @if ($publicRooms->isNotEmpty())
                <div class="mb-4 px-2">
                    <p class="mb-1 text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">{{ __('Public rooms') }}</p>
                    <div style="display:grid;gap:.5rem;grid-template-columns:repeat(auto-fill,minmax(220px,1fr))">
                        @foreach ($publicRooms as $public)
                            <a data-turbolinks="false" href="{{ route('nitro-client', ['room' => $public['id']]) }}" title="{{ __('Enter this room') }}" class="block overflow-hidden rounded bg-gray-100 dark:bg-gray-700 transition hover:ring-2 hover:ring-[#eeb425] focus:outline-none focus:ring-2 focus:ring-[#eeb425]">
                                @if ($public['image'] !== '')
                                    <img src="{{ $public['image'] }}" alt="" loading="lazy" class="h-24 w-full object-cover">
                                @endif
                                <div class="p-3">
                                    <p class="truncate text-sm font-semibold dark:text-gray-100">{{ $public['name'] }}</p>
                                    @if ($public['description'] !== '')
                                        <p class="mt-1 line-clamp-2 text-xs text-gray-500 dark:text-gray-400">{{ $public['description'] }}</p>
                                    @endif
                                    <p class="mt-2 text-[11px] text-gray-500 dark:text-gray-400">{{ $public['users'] }} / {{ $public['max'] }} {{ __('inside') }}</p>
                                    <p class="mt-2 text-[11px] font-semibold text-green-700 dark:text-green-400">{{ __('Enter room') }} &rarr;</p>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif

            @if ($weekly || $picks->isNotEmpty())
                <div class="mb-4 px-2">
                    @if ($weekly)
                        <a data-turbolinks="false" href="{{ route('nitro-client', ['room' => $weekly->room->id]) }}" class="block rounded border-2 border-yellow-400 bg-yellow-50 p-3 dark:bg-gray-700 transition hover:ring-2 hover:ring-[#eeb425] focus:outline-none focus:ring-2 focus:ring-[#eeb425]">
                            <p class="text-[11px] font-bold uppercase text-yellow-700 dark:text-yellow-300">{{ __('Room of the week') }}</p>
                            <p class="text-base font-semibold dark:text-gray-100">{{ $weekly->room->caption }}</p>
                            <p class="text-xs text-gray-600 dark:text-gray-300">{{ __('by :name', ['name' => $weekly->room->ownerUser?->username ?? '?']) }}</p>
                            @if ($weekly->note)
                                <p class="mt-1 text-xs text-gray-600 dark:text-gray-300">{{ $weekly->note }}</p>
                            @endif
                            <p class="mt-2 text-[11px] font-semibold text-green-700 dark:text-green-400">{{ __('Enter room') }} &rarr;</p>
                        </a>
                    @endif
                    @if ($picks->isNotEmpty())
                        <p class="mb-1 mt-3 text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">{{ __('Staff picks') }}</p>
                        <div style="display:grid;gap:.5rem;grid-template-columns:repeat(auto-fill,minmax(200px,1fr))">
                            @foreach ($picks as $pick)
                                <a data-turbolinks="false" href="{{ route('nitro-client', ['room' => $pick->room->id]) }}" class="block rounded bg-gray-100 p-2 dark:bg-gray-700 transition hover:ring-2 hover:ring-[#eeb425] focus:outline-none focus:ring-2 focus:ring-[#eeb425]">
                                    <p class="truncate text-sm font-semibold dark:text-gray-100">{{ $pick->room->caption }}</p>
                                    <p class="truncate text-xs text-gray-600 dark:text-gray-300">{{ $pick->note ?: __('by :name', ['name' => $pick->room->ownerUser?->username ?? '?']) }}</p>
                                </a>
                            @endforeach
                        </div>
                    @endif
                </div>
            @endif

            <form method="GET" action="{{ route('rooms.index') }}" class="mb-4 flex flex-wrap gap-2 px-2">
                <input type="search" name="q" value="{{ $search }}" placeholder="{{ __('Search rooms') }}" class="min-w-0 flex-1 rounded border-4 border-gray-200 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 focus:ring-0 focus:border-[#eeb425]">
                <select name="sort" class="rounded border-4 border-gray-200 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 focus:ring-0">
                    <option value="visitors" @selected($sort === 'users_now')>{{ __('Most visitors') }}</option>
                    <option value="likes" @selected($sort === 'score')>{{ __('Most likes') }}</option>
                </select>
                <button type="submit" class="rounded border-2 border-green-500 bg-green-600 px-4 font-semibold text-white hover:bg-green-700">{{ __('Search') }}</button>
            </form>

            @if ($rooms->isEmpty())
                <p class="px-2 text-sm text-gray-500 dark:text-gray-400">{{ __('No rooms match that search.') }}</p>
            @else
                <div class="px-2" style="display:grid;gap:.5rem;grid-template-columns:repeat(auto-fill,minmax(220px,1fr))">
                    @foreach ($rooms as $room)
                        <a data-turbolinks="false" href="{{ route('nitro-client', ['room' => $room->id]) }}" title="{{ __('Enter this room') }}" class="block rounded bg-gray-100 p-3 dark:bg-gray-700 transition hover:ring-2 hover:ring-[#eeb425] focus:outline-none focus:ring-2 focus:ring-[#eeb425]">
                            <p class="truncate text-sm font-semibold dark:text-gray-100">{{ $room->caption }}</p>
                            <p class="truncate text-xs text-gray-600 dark:text-gray-300">{{ __('by :name', ['name' => $room->ownerUser?->username ?? '?']) }}</p>
                            @if ($room->description)
                                <p class="mt-1 line-clamp-2 text-xs text-gray-500 dark:text-gray-400">{{ $room->description }}</p>
                            @endif
                            <p class="mt-2 text-[11px] text-gray-500 dark:text-gray-400">
                                {{ $room->users_now }} / {{ $room->users_max }} {{ __('inside') }} · {{ $room->score }} {{ __('likes') }}
                            </p>
                            <p class="mt-2 text-[11px] font-semibold text-green-700 dark:text-green-400">{{ __('Enter room') }} &rarr;</p>
                        </a>
                    @endforeach
                </div>

                <div class="mt-4 px-2">{{ $rooms->links() }}</div>
            @endif
        </x-content.content-card>
    </div>
</x-app-layout>

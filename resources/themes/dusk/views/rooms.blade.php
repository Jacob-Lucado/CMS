<x-app-layout>
    @push('title', __('Rooms'))

    <div class="col-span-12 flex flex-col gap-y-3">
        <x-content.content-card icon="hotel-icon" classes="border dark:border-gray-900">
            <x-slot:title>{{ __('Room browser') }}</x-slot:title>
            <x-slot:under-title>{{ __('Open rooms in the hotel. Search by room name or owner.') }}</x-slot:under-title>

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
                        <div class="rounded bg-gray-100 p-3 dark:bg-gray-700">
                            <p class="truncate text-sm font-semibold dark:text-gray-100">{{ $room->caption }}</p>
                            <p class="truncate text-xs text-gray-600 dark:text-gray-300">{{ __('by :name', ['name' => $room->ownerUser?->username ?? '?']) }}</p>
                            @if ($room->description)
                                <p class="mt-1 line-clamp-2 text-xs text-gray-500 dark:text-gray-400">{{ $room->description }}</p>
                            @endif
                            <p class="mt-2 text-[11px] text-gray-500 dark:text-gray-400">
                                {{ $room->users_now }} / {{ $room->users_max }} {{ __('inside') }} · {{ $room->score }} {{ __('likes') }}
                            </p>
                        </div>
                    @endforeach
                </div>

                <div class="mt-4 px-2">{{ $rooms->links() }}</div>
            @endif
        </x-content.content-card>
    </div>
</x-app-layout>

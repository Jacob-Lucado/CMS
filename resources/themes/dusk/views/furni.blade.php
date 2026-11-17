<x-app-layout>
    @push('title', __('Furni'))

    <div class="col-span-12 flex flex-col gap-y-3">
        <x-content.content-card icon="hotel-icon" classes="border dark:border-gray-900">
            <x-slot:title>{{ __('Furni browser') }}</x-slot:title>
            <x-slot:under-title>{{ __('Every piece of furniture in the hotel. Search by name.') }}</x-slot:under-title>

            <form method="GET" action="{{ route('furni.index') }}" class="mb-4 flex gap-2 px-2">
                <input type="search" name="q" value="{{ $search }}" placeholder="{{ __('Search furni') }}" class="min-w-0 flex-1 rounded border-4 border-gray-200 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 focus:ring-0 focus:border-[#eeb425]">
                <button type="submit" class="rounded border-2 border-green-500 bg-green-600 px-4 font-semibold text-white hover:bg-green-700">{{ __('Search') }}</button>
            </form>

            @if ($items->isEmpty())
                <p class="px-2 text-sm text-gray-500 dark:text-gray-400">{{ __('No furni match that search.') }}</p>
            @else
                <div class="px-2" style="display:grid;gap:.5rem;grid-template-columns:repeat(auto-fill,minmax(130px,1fr))">
                    @foreach ($items as $item)
                        <div class="flex flex-col items-center rounded bg-gray-100 p-3 text-center dark:bg-gray-700">
                            <div class="flex h-14 w-14 items-center justify-center">
                                <img src="{{ $iconBase }}/{{ $item->item_name }}_icon.png" alt="" loading="lazy" class="max-h-14 max-w-14" style="image-rendering:pixelated" onerror="this.style.visibility='hidden'">
                            </div>
                            <p class="mt-2 line-clamp-2 text-xs font-semibold dark:text-gray-100">{{ $item->public_name }}</p>
                            <p class="text-[11px] text-gray-500 dark:text-gray-400">{{ $item->width }}x{{ $item->length }}{{ $item->can_trade ? '' : ' · ' . __('no trade') }}</p>
                        </div>
                    @endforeach
                </div>

                <div class="mt-4 px-2">{{ $items->links() }}</div>
            @endif
        </x-content.content-card>
    </div>
</x-app-layout>

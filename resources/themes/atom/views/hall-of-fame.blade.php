<x-app-layout>
    @push('title', __('Hall of fame'))

    <div class="col-span-12 flex flex-col gap-y-3">
        <x-content.content-card icon="leaderboards-icon" classes="border dark:border-gray-900">
            <x-slot:title>{{ __('Hall of fame') }}</x-slot:title>
            <x-slot:under-title>{{ __('Players the team wants to thank.') }}</x-slot:under-title>

            <div class="px-2">
                @if ($entries->isEmpty())
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('Nobody here yet. Keep an eye on this page.') }}</p>
                @else
                    <div style="display:grid;gap:.75rem;grid-template-columns:repeat(auto-fill,minmax(220px,1fr))">
                        @foreach ($entries as $entry)
                            <div class="flex items-center gap-3 rounded border-2 border-yellow-300 bg-yellow-50 p-3 dark:border-yellow-600 dark:bg-gray-700">
                                @if ($entry->user)
                                    <div class="h-16 w-16 shrink-0 rounded bg-white bg-center bg-no-repeat dark:bg-gray-800" style="image-rendering:pixelated;background-image:url({{ setting('avatar_imager') }}{{ $entry->user->look }}&direction=2&head_direction=3&gesture=sml&size=m&headonly=1)"></div>
                                @endif
                                <div class="min-w-0">
                                    <p class="text-[11px] font-bold uppercase text-yellow-700 dark:text-yellow-300">{{ $entry->title }}</p>
                                    <p class="truncate font-semibold dark:text-gray-100">{{ $entry->user?->username ?? '?' }}</p>
                                    @if ($entry->note)<p class="text-xs text-gray-600 dark:text-gray-300">{{ $entry->note }}</p>@endif
                                    <p class="text-[11px] text-gray-500 dark:text-gray-400">{{ $entry->awarded_on->format('M j, Y') }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <div class="mt-3">{{ $entries->links() }}</div>
                @endif
            </div>
        </x-content.content-card>
    </div>
</x-app-layout>

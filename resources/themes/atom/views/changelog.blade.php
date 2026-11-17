<x-app-layout>
    @push('title', __('What is new'))

    <div class="col-span-12 flex flex-col gap-y-3">
        <x-content.content-card icon="leaderboards-icon" classes="border dark:border-gray-900">
            <x-slot:title>{{ __('What is new') }}</x-slot:title>
            <x-slot:under-title>{{ __('Updates, fixes and new features in the hotel.') }}</x-slot:under-title>

            <div class="space-y-3 px-2">
                @forelse ($entries as $entry)
                    <div class="rounded bg-gray-100 p-3 dark:bg-gray-700">
                        <p class="flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400">
                            <span class="rounded bg-yellow-400 px-1.5 py-0.5 font-bold uppercase text-gray-900">{{ match ($entry->category) { 'fix' => __('Fix'), 'event' => __('Event'), default => __('New') } }}</span>
                            {{ $entry->published_at?->format('M j, Y') }}
                        </p>
                        <p class="mt-1 font-semibold dark:text-gray-100">{{ $entry->title }}</p>
                        <p class="mt-1 whitespace-pre-line text-sm text-gray-700 dark:text-gray-300">{{ $entry->body }}</p>
                    </div>
                @empty
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('Nothing posted yet.') }}</p>
                @endforelse

                {{ $entries->links() }}
            </div>
        </x-content.content-card>
    </div>
</x-app-layout>

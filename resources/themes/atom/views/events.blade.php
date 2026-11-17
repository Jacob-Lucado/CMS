<x-app-layout>
    @push('title', __('Events'))

    <div class="col-span-12 flex flex-col gap-y-3">
        <x-content.content-card icon="hotel-icon" classes="border dark:border-gray-900">
            <x-slot:title>{{ __('Events') }}</x-slot:title>
            <x-slot:under-title>{{ __('What the team has planned in the hotel.') }}</x-slot:under-title>

            <div class="flex flex-col gap-2 px-2">
                @forelse ($upcoming as $event)
                    <div class="flex gap-3 rounded bg-gray-100 p-3 dark:bg-gray-700">
                        <div class="w-14 flex-none rounded bg-white py-1 text-center dark:bg-gray-800">
                            <p class="text-[11px] uppercase text-gray-500 dark:text-gray-400">{{ $event->starts_at->format('M') }}</p>
                            <p class="text-xl font-bold dark:text-gray-100">{{ $event->starts_at->format('j') }}</p>
                        </div>
                        <div class="min-w-0">
                            <p class="text-sm font-semibold dark:text-gray-100">{{ $event->title }}</p>
                            <p class="text-xs text-gray-600 dark:text-gray-300">
                                {{ $event->starts_at->format('g:i A') }}@if ($event->location) · {{ $event->location }}@endif
                            </p>
                            @if ($event->description)
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ $event->description }}</p>
                            @endif
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('No events planned right now. Check back soon.') }}</p>
                @endforelse
            </div>

            @if ($past->isNotEmpty())
                <h3 class="mt-6 px-2 text-sm font-semibold dark:text-gray-200">{{ __('Recent events') }}</h3>
                <ul class="mt-2 px-2 text-xs text-gray-500 dark:text-gray-400">
                    @foreach ($past as $event)
                        <li>{{ $event->starts_at->format('M j') }} · {{ $event->title }}</li>
                    @endforeach
                </ul>
            @endif
        </x-content.content-card>
    </div>
</x-app-layout>

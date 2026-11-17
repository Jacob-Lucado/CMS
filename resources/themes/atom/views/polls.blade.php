<x-app-layout>
    @push('title', __('Polls'))

    <div class="col-span-12 flex flex-col gap-y-3">
        <x-content.content-card icon="leaderboards-icon" classes="border dark:border-gray-900">
            <x-slot:title>{{ __('Polls') }}</x-slot:title>
            <x-slot:under-title>{{ __('Have your say on what happens next in the hotel.') }}</x-slot:under-title>

            <div class="space-y-4 px-2">
                @if (session('status'))
                    <p class="rounded bg-green-100 p-2 text-sm text-green-800">{{ session('status') }}</p>
                @endif

                @forelse ($polls as $poll)
                    @php
                        $rows = $counts->get($poll->id, collect())->pluck('total', 'option_index');
                        $total = max($rows->sum(), 1);
                        $voted = $mine->has($poll->id);
                    @endphp
                    <div class="rounded bg-gray-100 p-3 dark:bg-gray-700">
                        <p class="font-semibold dark:text-gray-100">{{ $poll->question }}</p>
                        <p class="mb-2 text-xs text-gray-500 dark:text-gray-400">{{ $poll->isOpen() ? ($poll->closes_at ? __('Closes :when', ['when' => $poll->closes_at->diffForHumans()]) : __('Open')) : __('Closed') }}</p>

                        @foreach ($poll->choices() as $index => $choice)
                            @php($percent = (int) round(($rows->get($index, 0) / $total) * 100))
                            @if ($poll->isOpen() && ! $voted && auth()->check())
                                <form method="POST" action="{{ route('polls.vote', $poll) }}" class="mb-1">
                                    @csrf
                                    <input type="hidden" name="option" value="{{ $index }}">
                                    <button class="w-full rounded border-2 border-gray-300 bg-white px-3 py-1.5 text-left text-sm hover:border-yellow-400 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100">{{ $choice }}</button>
                                </form>
                            @else
                                <div class="relative mb-1 overflow-hidden rounded border-2 {{ $mine->get($poll->id) === $index ? 'border-yellow-400' : 'border-gray-300 dark:border-gray-600' }} bg-white dark:bg-gray-800">
                                    <div class="absolute inset-y-0 left-0 bg-yellow-200 dark:bg-yellow-600/40" style="width: {{ $percent }}%"></div>
                                    <p class="relative flex justify-between px-3 py-1.5 text-sm dark:text-gray-100"><span>{{ $choice }}</span><span>{{ $percent }}%</span></p>
                                </div>
                            @endif
                        @endforeach
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ trans_choice(':count vote|:count votes', $rows->sum()) }}</p>
                    </div>
                @empty
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('No polls right now. Check back soon.') }}</p>
                @endforelse
            </div>
        </x-content.content-card>
    </div>
</x-app-layout>

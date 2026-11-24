<x-app-layout>
    @push('title', __('Room music'))

    <div class="col-span-12">
        <div class="rounded bg-white p-2 shadow-sm dark:bg-gray-900">
            <div class="text-center font-semibold text-gray-700 dark:text-gray-300">{{ __('Music played in :room', ['room' => $roomName ?? __('this room')]) }}</div>
            <hr class="dark:border-gray-500">
            <div class="mt-4 flex flex-col gap-y-3">
                @foreach ($plays as $play)
                    <div class="rounded bg-gray-100 p-3 dark:bg-gray-800">
                        @if (str_starts_with($play->url, 'https://'))
                            <a href="{{ $play->url }}" target="_blank" rel="noopener noreferrer" class="block truncate font-bold text-gray-700 hover:underline dark:text-gray-100">{{ \Illuminate\Support\Str::limit(preg_replace('#^https://(www\.)?#', '', $play->url), 60) }}</a>
                        @else
                            <p class="truncate font-bold text-gray-700 dark:text-gray-100">{{ \Illuminate\Support\Str::limit($play->url, 60) }}</p>
                        @endif
                        <p class="text-sm text-gray-600 dark:text-gray-300">{{ $play->set_by }} - {{ $play->played_at->diffForHumans() }}</p>
                    </div>
                @endforeach
            </div>
            <div class="mt-4">{{ $plays->links() }}</div>
            <div class="mt-2 text-center"><a href="{{ route('music.index') }}" class="text-sm text-gray-600 hover:underline dark:text-gray-300">{{ __('Back to music charts') }}</a></div>
        </div>
    </div>
</x-app-layout>

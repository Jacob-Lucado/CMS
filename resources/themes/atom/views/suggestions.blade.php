<x-app-layout>
    @push('title', __('Suggestion box'))

    <div class="col-span-12 flex flex-col gap-y-3">
        <x-content.content-card icon="leaderboards-icon" classes="border dark:border-gray-900">
            <x-slot:title>{{ __('Suggestion box') }}</x-slot:title>
            <x-slot:under-title>{{ __('Share an idea and vote for the ones you like.') }}</x-slot:under-title>

            <div class="space-y-3 px-2">
                @if (session('status'))
                    <p class="rounded bg-green-100 p-2 text-sm text-green-800">{{ session('status') }}</p>
                @endif

                @auth
                    <form method="POST" action="{{ route('suggestions.store') }}" class="space-y-2 rounded bg-gray-100 p-3 dark:bg-gray-700">
                        @csrf
                        <input type="text" name="title" value="{{ old('title') }}" minlength="5" maxlength="120" required placeholder="{{ __('Your idea in a few words') }}" class="w-full rounded border-2 border-gray-300 dark:bg-gray-800 dark:text-gray-100">
                        <textarea name="body" rows="3" minlength="15" maxlength="1500" required placeholder="{{ __('Tell us more') }}" class="w-full rounded border-2 border-gray-300 dark:bg-gray-800 dark:text-gray-100">{{ old('body') }}</textarea>
                        @foreach ($errors->all() as $error)<p class="text-sm text-red-600">{{ $error }}</p>@endforeach
                        <button class="rounded border-2 border-green-500 bg-green-600 px-4 py-1.5 font-semibold text-white hover:bg-green-700">{{ __('Post idea') }}</button>
                    </form>
                @endauth

                <div class="flex flex-wrap gap-1 text-xs">
                    <a href="{{ route('suggestions.index') }}" class="rounded px-2 py-1 {{ $status ? 'bg-gray-100 dark:bg-gray-700 dark:text-gray-100' : 'bg-yellow-400 font-bold' }}">{{ __('All') }}</a>
                    @foreach (['open' => __('Open'), 'planned' => __('Planned'), 'done' => __('Done'), 'declined' => __('Declined')] as $key => $label)
                        <a href="{{ route('suggestions.index', ['status' => $key]) }}" class="rounded px-2 py-1 {{ $status === $key ? 'bg-yellow-400 font-bold' : 'bg-gray-100 dark:bg-gray-700 dark:text-gray-100' }}">{{ $label }}</a>
                    @endforeach
                </div>

                @forelse ($suggestions as $suggestion)
                    <div class="flex gap-3 rounded bg-gray-100 p-3 dark:bg-gray-700">
                        <form method="POST" action="{{ route('suggestions.vote', $suggestion) }}" class="shrink-0">
                            @csrf
                            <button @guest disabled @endguest class="flex h-14 w-12 flex-col items-center justify-center rounded border-2 {{ in_array($suggestion->id, $voted, true) ? 'border-yellow-400 bg-yellow-100 dark:bg-yellow-600/40' : 'border-gray-300 bg-white dark:border-gray-600 dark:bg-gray-800' }} dark:text-gray-100" aria-label="{{ __('Vote') }}">
                                <span class="text-xs">▲</span><span class="text-sm font-bold">{{ $suggestion->votes_count }}</span>
                            </button>
                        </form>
                        <div class="min-w-0">
                            <p class="font-semibold dark:text-gray-100">{{ $suggestion->title }}
                                @if ($suggestion->status !== 'open')
                                    <span class="ml-1 rounded bg-yellow-400 px-1.5 py-0.5 text-[10px] font-bold uppercase text-gray-900">{{ $suggestion->status }}</span>
                                @endif
                            </p>
                            <p class="whitespace-pre-line text-sm text-gray-700 dark:text-gray-300">{{ $suggestion->body }}</p>
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ __('by :name', ['name' => $suggestion->author?->username ?? '?']) }} · {{ $suggestion->created_at?->diffForHumans() }}</p>
                            @if ($suggestion->staff_reply)
                                <p class="mt-2 rounded border-l-4 border-yellow-400 bg-white p-2 text-sm dark:bg-gray-800 dark:text-gray-100"><b>{{ __('Staff') }}:</b> {{ $suggestion->staff_reply }}</p>
                            @endif
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('No ideas here yet. Be the first!') }}</p>
                @endforelse

                {{ $suggestions->links() }}
            </div>
        </x-content.content-card>
    </div>
</x-app-layout>

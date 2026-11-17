<x-app-layout>
    @push('title', __('Riddle of the day'))

    <div class="col-span-12 flex flex-col gap-y-3">
        <x-content.content-card icon="leaderboards-icon" classes="border dark:border-gray-900">
            <x-slot:title>{{ __('Riddle of the day') }}</x-slot:title>
            <x-slot:under-title>{{ __('Solve it for credits. A new one shows up each day.') }}</x-slot:under-title>

            <div class="px-2">
                @if (session('status'))
                    <p class="mb-3 rounded bg-green-100 p-2 text-sm text-green-800">{{ session('status') }}</p>
                @endif

                @if (! $riddle)
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('No riddle today. Check back tomorrow.') }}</p>
                @else
                    <div class="rounded bg-gray-100 p-4 dark:bg-gray-700">
                        <p class="text-lg font-semibold dark:text-gray-100">{{ $riddle->question }}</p>
                        @if ($riddle->hint)
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ __('Hint: :hint', ['hint' => $riddle->hint]) }}</p>
                        @endif
                        <p class="mt-2 text-xs text-gray-600 dark:text-gray-300">{{ __('Reward: :n credits', ['n' => $riddle->reward_credits]) }} · {{ trans_choice(':count player solved it|:count players solved it', $solvers) }}</p>

                        @auth
                            @if ($solved)
                                <p class="mt-3 text-sm font-semibold text-green-700 dark:text-green-400">{{ __('You solved it. Come back tomorrow.') }}</p>
                            @else
                                <form method="POST" action="{{ route('riddle.answer') }}" class="mt-3 flex gap-2">
                                    @csrf
                                    <input type="text" name="answer" value="{{ old('answer') }}" maxlength="100" required autocomplete="off" placeholder="{{ __('Your answer') }}" class="w-full rounded border-2 border-gray-300 dark:bg-gray-800 dark:text-gray-100">
                                    <button class="rounded border-2 border-green-500 bg-green-600 px-4 font-semibold text-white hover:bg-green-700">{{ __('Answer') }}</button>
                                </form>
                                @error('answer')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                            @endif
                        @else
                            <p class="mt-3 text-sm dark:text-gray-200">{{ __('Log in to answer.') }}</p>
                        @endauth
                    </div>
                @endif
            </div>
        </x-content.content-card>
    </div>
</x-app-layout>

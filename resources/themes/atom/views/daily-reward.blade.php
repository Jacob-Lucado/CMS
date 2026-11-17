<x-app-layout>
    @push('title', __('Daily reward'))

    <div class="col-span-12 flex flex-col gap-y-3">
        <x-content.content-card icon="currency-icon" classes="border dark:border-gray-900">
            <x-slot:title>{{ __('Daily reward') }}</x-slot:title>
            <x-slot:under-title>{{ __('Claim once a day. Keep a streak going for a bigger reward.') }}</x-slot:under-title>

            <div class="px-2">
                @if (session('status'))
                    <p class="mb-3 rounded bg-green-100 p-2 text-sm text-green-800">{{ session('status') }}</p>
                @endif

                <div style="display:grid;gap:.5rem;grid-template-columns:repeat(7,minmax(0,1fr))">
                    @for ($day = 1; $day <= 7; $day++)
                        @php($done = $day <= min($streak, 7) && $streak > 0)
                        <div class="rounded p-2 text-center {{ $done ? 'bg-green-600 text-white' : 'bg-gray-100 dark:bg-gray-700 dark:text-gray-100' }}">
                            <p class="text-[11px] uppercase">{{ __('Day :n', ['n' => $day]) }}</p>
                            <p class="text-sm font-bold">{{ \App\Models\DailyReward::amountFor($day) }}</p>
                        </div>
                    @endfor
                </div>

                <p class="mt-3 text-sm dark:text-gray-200">
                    {{ trans_choice('Current streak: :count day|Current streak: :count days', $streak) }}
                    · {{ __('Best: :n', ['n' => (int) $reward->best_streak]) }}
                    · {{ __('Total claims: :n', ['n' => (int) $reward->total_claims]) }}
                </p>

                <form method="POST" action="{{ route('daily-reward.claim') }}" class="mt-4">
                    @csrf
                    @if ($claimed)
                        <button type="button" disabled class="rounded border-2 border-gray-300 bg-gray-200 px-4 py-2 font-semibold text-gray-500">{{ __('Claimed. Next reward: :amount credits tomorrow', ['amount' => $amount]) }}</button>
                    @else
                        <button type="submit" class="rounded border-2 border-green-500 bg-green-600 px-4 py-2 font-semibold text-white hover:bg-green-700">{{ __('Claim :amount credits', ['amount' => $amount]) }}</button>
                    @endif
                </form>
            </div>
        </x-content.content-card>
    </div>
</x-app-layout>

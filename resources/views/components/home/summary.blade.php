@props(['user'])

@php
    $streak = \App\Models\DailyReward::query()->where('user_id', $user->id)->first();
    $stats = [
        __('Member since') => \Illuminate\Support\Carbon::createFromTimestamp($user->account_created)->format('M j, Y'),
        __('Last seen') => $user->online ? __('Online now') : \Illuminate\Support\Carbon::createFromTimestamp($user->last_online)->diffForHumans(),
        __('Badges') => number_format($user->emulatorBadges()->count()),
        __('Rooms') => number_format(app(\App\Emulator\Contracts\RoomRepository::class)->forHome($user)->count()),
        __('Best streak') => number_format((int) $streak?->best_streak),
    ];
@endphp

<div class="w-full max-w-[928px] rounded border bg-white p-3 shadow-sm dark:border-gray-900 dark:bg-gray-800" style="display:grid;gap:.5rem;grid-template-columns:repeat(auto-fit,minmax(130px,1fr))">
    @foreach ($stats as $label => $value)
        <div class="rounded bg-gray-100 p-2 text-center dark:bg-gray-700">
            <p class="text-[11px] font-bold uppercase text-gray-500 dark:text-gray-400">{{ $label }}</p>
            <p class="text-sm font-semibold dark:text-gray-100">{{ $value }}</p>
        </div>
    @endforeach
</div>

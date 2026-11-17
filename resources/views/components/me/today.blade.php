@php
    $reward = \App\Models\DailyReward::query()->where('user_id', auth()->id())->first();
    $claimed = $reward?->claimedToday() ?? false;
    $weekly = \App\Models\FeaturedRoom::query()->with('room')->where('room_of_the_week', true)->first();
    $event = \App\Models\WebsiteEvent::query()->where('published', true)->where('starts_at', '>=', now())->orderBy('starts_at')->first();
@endphp

<div class="rounded border bg-white p-3 shadow-sm dark:border-gray-900 dark:bg-gray-800 dark:text-gray-100" style="display:grid;gap:.75rem;grid-template-columns:repeat(auto-fit,minmax(200px,1fr))">
    <a href="{{ route('daily-reward.show') }}" class="block rounded bg-gray-100 p-3 dark:bg-gray-700">
        <p class="text-[11px] font-bold uppercase text-gray-500 dark:text-gray-400">{{ __('Daily reward') }}</p>
        <p class="text-sm font-semibold">{{ $claimed ? __('Claimed today') : __('Ready to claim') }}</p>
    </a>

    @if ($weekly?->room)
        <a href="{{ route('rooms.index') }}" class="block rounded bg-gray-100 p-3 dark:bg-gray-700">
            <p class="text-[11px] font-bold uppercase text-gray-500 dark:text-gray-400">{{ __('Room of the week') }}</p>
            <p class="truncate text-sm font-semibold">{{ $weekly->room->caption }}</p>
        </a>
    @endif

    @if ($event)
        <a href="{{ route('events.index') }}" class="block rounded bg-gray-100 p-3 dark:bg-gray-700">
            <p class="text-[11px] font-bold uppercase text-gray-500 dark:text-gray-400">{{ __('Next event') }}</p>
            <p class="truncate text-sm font-semibold">{{ $event->title }}</p>
            <p class="text-xs text-gray-600 dark:text-gray-300">{{ $event->starts_at->format('M j, g:i A') }}</p>
        </a>
    @endif
</div>

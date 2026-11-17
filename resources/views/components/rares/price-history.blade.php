@props(['history'])

@php
    $points = $history->map(fn ($row) => ['at' => $row->created_at, 'credits' => (float) preg_replace('/[^0-9.]/', '', (string) $row->credit_value)])->values();
    $max = max($points->max('credits') ?? 0, 1);
    $min = $points->min('credits') ?? 0;
    $span = max($max - $min, 1);
    $count = max($points->count() - 1, 1);
    $coords = $points->map(fn ($p, $i) => round(8 + ($i / $count) * 284, 1) . ',' . round(8 + (1 - ($p['credits'] - $min) / $span) * 64, 1))->implode(' ');
@endphp

@if ($points->count() >= 2)
    <x-content.content-card icon="currency-icon">
        <x-slot:title>{{ __('Price history') }}</x-slot:title>
        <x-slot:under-title>{{ __('Credit value each time it changed') }}</x-slot:under-title>

        <svg viewBox="0 0 300 80" preserveAspectRatio="none" class="h-24 w-full text-yellow-500" role="img" aria-label="{{ __('Price history') }}">
            <polyline points="{{ $coords }}" fill="none" stroke="currentColor" stroke-width="2" vector-effect="non-scaling-stroke" stroke-linejoin="round" stroke-linecap="round" />
        </svg>
        <p class="mt-1 flex justify-between text-xs text-gray-600 dark:text-gray-300">
            <span>{{ $points->first()['at']?->format('M j, Y') }}: {{ number_format($points->first()['credits']) }}</span>
            <span>{{ $points->last()['at']?->format('M j, Y') }}: {{ number_format($points->last()['credits']) }}</span>
        </p>
    </x-content.content-card>
@endif

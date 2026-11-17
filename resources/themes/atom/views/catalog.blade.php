<x-app-layout>
    @push('title', __('Catalog'))

    <div class="col-span-12 flex flex-col gap-y-3">
        <x-content.content-card icon="catalog-icon" classes="border dark:border-gray-900">
            <x-slot:title>{{ __('Catalog browser') }}</x-slot:title>
            <x-slot:under-title>{{ __('See what is for sale in the hotel and what it costs.') }}</x-slot:under-title>

            @if ($roots->isEmpty())
                <p class="px-2 text-sm text-gray-500 dark:text-gray-400">{{ __('The catalog is not available right now.') }}</p>
            @else
                <div class="px-2" style="display:grid;gap:1rem;grid-template-columns:minmax(180px,240px) 1fr">
                    <nav class="text-sm">
                        @foreach ($roots as $root)
                            <a href="{{ route('catalog.index', ['p' => $root->id]) }}" class="block rounded px-2 py-1 font-semibold hover:bg-gray-100 dark:text-gray-100 dark:hover:bg-gray-700 {{ $current?->id === $root->id ? 'bg-gray-100 dark:bg-gray-700' : '' }}">{{ $root->caption }}</a>
                            @foreach ($children->get($root->id, []) as $child)
                                <a href="{{ route('catalog.index', ['p' => $child->id]) }}" class="block rounded py-0.5 pl-6 pr-2 text-xs text-gray-600 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700 {{ $current?->id === $child->id ? 'bg-gray-100 font-semibold dark:bg-gray-700' : '' }}">{{ $child->caption }}</a>
                            @endforeach
                        @endforeach
                    </nav>

                    <div>
                        @if (! $current)
                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('Pick a page on the left to see its items.') }}</p>
                        @else
                            <h3 class="mb-2 text-sm font-semibold dark:text-gray-100">{{ $current->caption }}</h3>
                            @if ($offers->isEmpty())
                                <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('Nothing is for sale on this page.') }}</p>
                            @else
                                <div style="display:grid;gap:.5rem;grid-template-columns:repeat(auto-fill,minmax(150px,1fr))">
                                    @foreach ($offers as $offer)
                                        <div class="flex flex-col items-center rounded bg-gray-100 p-3 text-center dark:bg-gray-700">
                                            <div class="flex h-12 w-12 items-center justify-center">
                                                @if ($offer->icon)
                                                    <img src="{{ $iconBase }}/{{ $offer->icon }}" alt="" loading="lazy" class="max-h-12 max-w-12" style="image-rendering:pixelated" onerror="this.style.visibility='hidden'">
                                                @endif
                                            </div>
                                            <p class="mt-2 line-clamp-2 text-xs font-semibold dark:text-gray-100">{{ $offer->name }}</p>
                                            <p class="mt-1 text-[11px] text-gray-600 dark:text-gray-300">
                                                @if ($offer->cost_credits === 0 && $offer->cost_points === 0)
                                                    {{ __('Free') }}
                                                @else
                                                    @if ($offer->cost_credits > 0){{ number_format($offer->cost_credits) }} {{ __('credits') }}@endif
                                                    @if ($offer->cost_credits > 0 && $offer->cost_points > 0) + @endif
                                                    @if ($offer->cost_points > 0){{ number_format($offer->cost_points) }} {{ $offer->points_type === 5 ? __('diamonds') : ($offer->points_type === 0 ? __('duckets') : __('points')) }}@endif
                                                @endif
                                            </p>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        @endif
                    </div>
                </div>
            @endif
        </x-content.content-card>
    </div>
</x-app-layout>

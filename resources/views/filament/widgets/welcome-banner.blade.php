<x-filament-widgets::widget>
    <div class="hk-welcome">
        <div class="hk-welcome-text">
            <h2>{{ $this->greeting() }}, {{ $this->name() }}</h2>
            <p>{{ trans_choice(':count player is online right now.|:count players are online right now.', $this->online()) }}</p>
        </div>

        @if (count($links = $this->links()) > 0)
            <nav class="hk-welcome-links" aria-label="{{ __('Quick links') }}">
                @foreach ($links as $link)
                    <a href="{{ $link['url'] }}" class="hk-chip">
                        <x-filament::icon :icon="$link['icon']" class="hk-chip-icon" />
                        {{ $link['label'] }}
                    </a>
                @endforeach
            </nav>
        @endif
    </div>
</x-filament-widgets::widget>

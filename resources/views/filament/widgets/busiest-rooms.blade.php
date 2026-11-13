<x-filament-widgets::widget>
    <x-filament::section :heading="__('Busiest rooms')" :description="__('Where players are hanging out right now.')" icon="heroicon-o-fire">
        @php($rooms = $this->rooms())
        @if ($rooms->isEmpty())
            <p class="hk-empty">{{ __('Nobody is in a room right now.') }}</p>
        @else
            <ul class="hk-rows">
                @foreach ($rooms as $room)
                    <li>
                        <span class="hk-row-main">
                            <strong>{{ $room->name }}</strong>
                            <small>{{ __('by :owner', ['owner' => $room->ownerName ?: __('nobody')]) }}</small>
                        </span>
                        <span class="hk-count">{{ trans_choice(':count player|:count players', $room->playersInside) }}</span>
                    </li>
                @endforeach
            </ul>
        @endif
    </x-filament::section>
</x-filament-widgets::widget>

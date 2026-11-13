<x-filament-panels::page>
    @php($serverOnline = $this->gameServerOnline())

    <div class="hk-status">
        <div class="hk-status-tile">
            <span>{{ __('Players online') }}</span>
            <strong>{{ number_format($this->onlineCount()) }}</strong>
        </div>
        <div @class(['hk-status-tile', 'is-bad' => ! $serverOnline])>
            <span>{{ __('Game server') }}</span>
            <strong>{{ $serverOnline ? __('Online') : __('Offline') }}</strong>
        </div>
    </div>

    @unless ($serverOnline)
        <x-filament::section>
            <p class="hk-empty">{{ __('The game server is not answering, so alerts, gifts and reloads will not reach the hotel until it is back.') }}</p>
        </x-filament::section>
    @endunless

    <x-filament::section :heading="__('Everyone online')" :description="__('Goes to every player in the hotel right now, live.')" icon="heroicon-o-users">
        <div class="hk-actions">
            {{ $this->alertEveryoneAction }}
            {{ $this->giveBadgeAction }}
            {{ $this->giveCurrencyAction }}
        </div>
    </x-filament::section>

    <x-filament::section :heading="__('Reload')" :description="__('Makes the game server pick up changes you made in Housekeeping or the database, without a restart.')" icon="heroicon-o-arrow-path">
        <div class="hk-actions">
            @foreach ($this->reloads() as $key => $label)
                {{ ($this->reloadAction)(['what' => $key])->label($label)->icon('heroicon-o-arrow-path')->color('gray') }}
            @endforeach
        </div>
    </x-filament::section>

    <x-filament-actions::modals />
</x-filament-panels::page>

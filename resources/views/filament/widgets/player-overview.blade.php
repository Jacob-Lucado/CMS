@php
    $player = $this->player();
    $discord = $player->discordAccount;
    $others = $this->sharedAddressAccounts();
@endphp
<x-filament-widgets::widget>
    <div class="hk-overview">
        <x-filament::section :heading="__('Discord')" icon="heroicon-o-chat-bubble-left-right" compact>
            @if ($discord)
                <div class="hk-discord">
                    @if ($discord->avatarUrl())
                        <img src="{{ $discord->avatarUrl() }}" alt="">
                    @endif
                    <div>
                        <strong>{{ $discord->discord_username }}</strong>
                        <small>{{ $discord->discord_id }} · {{ __('linked :when', ['when' => $discord->created_at?->diffForHumans()]) }}</small>
                    </div>
                </div>
            @else
                <p class="hk-empty">{{ __('Not linked.') }}</p>
            @endif
        </x-filament::section>

        <x-filament::section :heading="__('Connections')" icon="heroicon-o-globe-alt" compact>
            <dl class="hk-facts">
                <dt>{{ __('Status') }}</dt>
                <dd>{{ $player->online ? __('In the hotel now') : __('Offline') }}</dd>
                <dt>{{ __('Signed up from') }}</dt>
                <dd class="hk-mono">{{ $player->ip_register ?: '-' }}</dd>
                <dt>{{ __('Last seen from') }}</dt>
                <dd class="hk-mono">{{ $player->ip_current ?: '-' }}</dd>
            </dl>
        </x-filament::section>

        <x-filament::section :heading="__('Same address')" :description="__('Other accounts that signed up or played from these addresses.')" icon="heroicon-o-users" compact>
            @if ($others->isEmpty())
                <p class="hk-empty">{{ __('No other accounts.') }}</p>
            @else
                <ul class="hk-rows">
                    @foreach ($others as $other)
                        <li>
                            <a class="hk-row-main" href="{{ \App\Filament\Resources\User\Users\UserResource::getUrl('edit', ['record' => $other]) }}">
                                <strong>{{ $other->username }}</strong>
                                <small class="hk-mono">{{ $other->ip_current ?: $other->ip_register }}</small>
                            </a>
                        </li>
                    @endforeach
                </ul>
            @endif
        </x-filament::section>
    </div>
</x-filament-widgets::widget>

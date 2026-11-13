@props([
    'title',
    'icon',
    'data',
    'valueType',
    'formatValue' => null,
])
<section class="los-card">
    <header class="los-card-head">
        <span class="los-head-emoji"><img src="{{ asset('/assets/images/icons/' . $icon) }}" alt="" style="image-rendering: pixelated;"></span>
        <div>
            <h2 class="los-card-title">{{ __($title) }}</h2>
        </div>
    </header>
    <ol class="los-ranks">
        @forelse ($data as $index => $entry)
            <li @class(['is-top' => $index < 3])>
                <span class="los-rank-place">{{ $index + 1 }}</span>
                <img alt="" src="{{ setting('avatar_imager') }}{{ $entry->user->look }}&size=m&head_direction=2&gesture=sml&headonly=1">
                <span class="los-rank-name">
                    <strong>{{ $entry->user->username }}</strong>
                    <small>{{ $formatValue ? $formatValue($entry->value) : number_format((int) $entry->value) }} {{ $valueType }}</small>
                </span>
            </li>
        @empty
            <li class="los-empty">{{ __('Nobody here yet.') }}</li>
        @endforelse
    </ol>
</section>

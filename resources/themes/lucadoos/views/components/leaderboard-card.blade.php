@props([
    'title',
    'icon',
    'data',
    'valueType',
    'formatValue' => null,
])
@php
    $board = \Illuminate\Support\Str::slug($title, '_');
    $kudos = \App\Models\Social\Kudos::query()->whereIn('to_user_id', collect($data)->pluck('user.id')->filter())->selectRaw('to_user_id, count(*) as total')->groupBy('to_user_id')->pluck('total', 'to_user_id');
@endphp
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
                @auth
                    @if (auth()->id() !== $entry->user->id)
                        <button type="button" class="los-kudos" data-kudos="{{ $entry->user->id }}" data-board="{{ $board }}" title="{{ __('Give :name kudos', ['name' => $entry->user->username]) }}">👏 <b>{{ (int) ($kudos[$entry->user->id] ?? 0) }}</b></button>
                    @else
                        <span class="los-kudos is-me" title="{{ __('Kudos') }}">👏 <b>{{ (int) ($kudos[$entry->user->id] ?? 0) }}</b></span>
                    @endif
                @endauth
            </li>
        @empty
            <li class="los-empty">{{ __('Nobody here yet.') }}</li>
        @endforelse
    </ol>
</section>

@once
    @push('javascript')
        <script>
            document.addEventListener('click', async (event) => {
                const button = event.target.closest('[data-kudos]');
                if (!button || button.disabled) return;
                button.disabled = true;
                const res = await fetch(@js(route('kudos.store', absolute: false)), {
                    method: 'POST', credentials: 'same-origin',
                    headers: { Accept: 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '' },
                    body: JSON.stringify({ user_id: Number(button.dataset.kudos), board: button.dataset.board }),
                }).catch(() => null);
                const data = res ? await res.json().catch(() => ({})) : {};
                if (res?.ok) button.querySelector('b').textContent = data.kudos;
                button.classList.add(res?.ok ? 'is-given' : 'is-denied');
                button.title = data.message ?? (data.errors ? Object.values(data.errors)[0][0] : button.title);
            });
        </script>
    @endpush
@endonce

<x-app-layout>
    @push('title', __('Gameboards'))

    <div class="col-span-12 gb">
        <section class="gb-card gb-intro">
            <b class="gb-title">🎮 {{ __('Gameboards') }}</b>
            <p class="gb-muted">{{ __('The top 5 on the hotel\'s game scoreboards, straight from the rooms. Give the champs some kudos!') }}</p>
        </section>

        @forelse ($boards as $board)
            <section class="gb-card">
                <div class="gb-head">
                    <div>
                        <b>{{ $board['name'] }}</b>
                        @if ($board['description'])
                            <p class="gb-muted">{{ $board['description'] }}</p>
                        @endif
                    </div>
                </div>
                <ol class="gb-list">
                    @forelse ($board['scores'] as $place => $row)
                        <li class="gb-row">
                            <span class="gb-place">{{ ['🥇', '🥈', '🥉'][$place] ?? '#' . ($place + 1) }}</span>
                            <span class="gb-players">
                                @foreach ($row['players'] as $player)
                                    <span class="gb-player">
                                        @if ($player['head'])
                                            <img src="{{ $player['head'] }}" alt="">
                                        @endif
                                        <a href="{{ route('home.show', $player['name']) }}">{{ $player['name'] }}</a>
                                        @if ($player['id'] && $player['id'] !== $viewer->id)
                                            <button type="button" class="los-kudos gb-kudos" data-game-kudos="{{ $player['id'] }}" data-board="game_{{ $board['id'] }}" title="{{ __('Give :name kudos', ['name' => $player['name']]) }}">👏 <b>{{ $player['kudos'] }}</b></button>
                                        @elseif ($player['id'])
                                            <span class="los-kudos gb-kudos is-me">👏 <b>{{ $player['kudos'] }}</b></span>
                                        @endif
                                    </span>
                                @endforeach
                            </span>
                            <b class="gb-score">{{ number_format($row['score']) }}</b>
                        </li>
                    @empty
                        <li class="gb-muted">{{ __('No scores yet. Go play!') }}</li>
                    @endforelse
                </ol>
            </section>
        @empty
            <p class="gb-card gb-muted">{{ __('No gameboards yet. Staff can link scoreboards in Housekeeping.') }}</p>
        @endforelse
    </div>

    @push('javascript')
        <style>
            .gb { display: grid; gap: 14px; grid-template-columns: repeat(auto-fill, minmax(340px, 1fr)); align-items: start; }
            .gb-intro { grid-column: 1 / -1; }
            .gb-card { background: var(--panel, rgba(127, 127, 127, .08)); border: 1px solid var(--line, rgba(127, 127, 127, .2)); border-radius: var(--radius, 14px); padding: 14px; display: grid; gap: 8px; margin: 0; }
            .gb-title { font-size: 18px; }
            .gb-muted { color: var(--muted, #8396ad); font-size: 13px; margin: 0; }
            .gb-list { list-style: none; margin: 0; padding: 0; display: grid; gap: 6px; }
            .gb-row { display: flex; gap: 10px; align-items: center; padding: 6px 8px; border-radius: 10px; background: var(--soft, rgba(127, 127, 127, .1)); }
            .gb-place { width: 28px; text-align: center; font-weight: 700; }
            .gb-players { flex: 1; display: grid; gap: 4px; min-width: 0; }
            .gb-player { display: flex; gap: 6px; align-items: center; }
            .gb-player img { width: 28px; height: 28px; object-fit: none; }
            .gb-player a { color: var(--white, inherit); font-weight: 600; text-decoration: none; }
            .gb-kudos { margin-left: auto; display: inline-flex; gap: 4px; align-items: center; padding: 2px 8px; border: 1px solid var(--line, rgba(127, 127, 127, .3)); border-radius: 999px; background: none; color: inherit; font: inherit; font-size: 12px; cursor: pointer; }
            .gb-kudos.is-me { cursor: default; opacity: .75; }
            .gb-kudos.is-given { border-color: #3ddc97; } .gb-kudos.is-denied { border-color: #e5484d; }
            .gb-score { font-variant-numeric: tabular-nums; }
        </style>
        <script>
            document.addEventListener('click', async (event) => {
                const button = event.target.closest('[data-game-kudos]');
                if (!button || button.disabled) return;
                button.disabled = true;
                const res = await fetch(@js($kudosUrl), {
                    method: 'POST', credentials: 'same-origin',
                    headers: { Accept: 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '' },
                    body: JSON.stringify({ user_id: Number(button.dataset.gameKudos), board: button.dataset.board }),
                }).catch(() => null);
                const data = res ? await res.json().catch(() => ({})) : {};
                if (res?.ok) button.querySelector('b').textContent = data.kudos;
                button.classList.add(res?.ok ? 'is-given' : 'is-denied');
                button.title = data.message ?? (data.errors ? Object.values(data.errors)[0][0] : button.title);
            });
        </script>
    @endpush
</x-app-layout>

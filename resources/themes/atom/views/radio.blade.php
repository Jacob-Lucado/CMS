<x-app-layout>
    @push('title', __('Radio'))

    <div class="col-span-12 hr" x-data="hotelRadio(@js([
        'feed' => route('radio.feed', absolute: false),
        'store' => route('radio.store', absolute: false),
        'onAir' => route('radio.on-air', absolute: false),
        'base' => rtrim(route('radio.index', absolute: false), '/'),
    ]))" x-init="start()">
        <section class="hr-win hr-main">
            <header class="hr-bar">
                <svg class="hr-logo" viewBox="0 0 16 16" width="22" height="22" shape-rendering="crispEdges" aria-hidden="true"><rect x="10" y="1" width="1" height="4" fill="#cfd8dc"/><rect x="9" y="4" width="2" height="1" fill="#cfd8dc"/><rect x="1" y="5" width="14" height="10" fill="#e8962a"/><rect x="1" y="5" width="14" height="1" fill="#f7c35f"/><rect x="1" y="14" width="14" height="1" fill="#a85f12"/><rect x="3" y="7" width="5" height="6" fill="#5a3a12"/><rect x="4" y="8" width="3" height="1" fill="#8a5a22"/><rect x="4" y="10" width="3" height="1" fill="#8a5a22"/><rect x="10" y="7" width="3" height="2" fill="#8dff8a"/><rect x="10" y="10" width="1" height="2" fill="#f2efe6"/><rect x="12" y="10" width="1" height="2" fill="#f2efe6"/></svg>
                <b>{{ __('Hotel Radio') }}</b>
                <span class="hr-lamp" :class="live && 'lit'"><i></i>{{ __('ON AIR') }}</span>
            </header>
            <div class="hr-pane">
                <div class="hr-lcd" :class="playing && 'live'">
                    <template x-if="now && now.art"><img class="hr-art" :src="now.art" alt=""></template>
                    <div class="hr-readout">
                        <small x-text="screen().tag"></small>
                        <b class="hr-big" x-text="screen().title"></b>
                        <span x-text="screen().sub"></span>
                        <small x-show="now && now.by" x-text="@js(__('Added by')) + ' ' + (now ? now.by : '')"></small>
                    </div>
                    <div class="hr-eq" aria-hidden="true">@for ($i = 0; $i < 16; $i++)<i style="--d: {{ ($i * 5) % 11 }}"></i>@endfor</div>
                </div>

                <div class="hr-stage" x-show="now"></div>

                <div class="hr-deck">
                    <div class="hr-ctl">
                        <button type="button" class="hr-btn hr-sq" :disabled="!now || now.kind !== 'track'" x-on:click="step(-1)" title="{{ __('Previous') }}" aria-label="{{ __('Previous') }}">&#9198;&#xFE0E;</button>
                        <button type="button" class="hr-btn hr-sq hr-primary" :disabled="!now" x-on:click="toggle()" :title="playing ? @js(__('Pause')) : @js(__('Play'))" :aria-label="playing ? @js(__('Pause')) : @js(__('Play'))" x-text="playing ? '❚❚' : '▶'"></button>
                        <button type="button" class="hr-btn hr-sq" :disabled="!now || now.kind !== 'track'" x-on:click="step(1)" title="{{ __('Next') }}" aria-label="{{ __('Next') }}">&#9197;&#xFE0E;</button>
                        <button type="button" class="hr-btn hr-sq" :disabled="!now" x-on:click="stop()" title="{{ __('Stop') }}" aria-label="{{ __('Stop') }}">■</button>
                    </div>
                    <label class="hr-vol">
                        <span>{{ __('VOL') }}</span>
                        <input type="range" min="0" max="1" step="0.05" x-model.number="volume" x-on:input="setVolume()" aria-label="{{ __('Volume') }}">
                    </label>
                </div>

                <div class="hr-ctl hr-more" x-show="now || onAir">
                    <button type="button" class="hr-btn hr-blue" x-show="onAir && !live" x-on:click="tuneIn()" x-text="now ? @js(__('Back to on air')) : @js(__('Tune in'))"></button>
                    <button type="button" class="hr-btn" x-show="now && now.kind === 'track'" :class="auto && 'on'" x-on:click="auto = !auto; remember()">&#10227; {{ __('Auto-next') }}</button>
                    <template x-if="dj && now">
                        <span class="hr-ctl">
                            <button type="button" class="hr-btn hr-primary" x-show="!onAir || onAir.id !== now.id" x-on:click="putOnAir(now.id)">{{ __('Put on air') }}</button>
                            <button type="button" class="hr-btn hr-red" x-show="onAir && onAir.id === now.id" x-on:click="putOnAir(null)">{{ __('Take off air') }}</button>
                        </span>
                    </template>
                    <a class="hr-btn" :href="now ? now.url : '#'" target="_blank" rel="noopener noreferrer" x-show="now && now.kind === 'track'">{{ __('Open on SoundCloud') }} &#8599;</a>
                </div>
            </div>
        </section>

        <aside class="hr-win hr-side">
            <header class="hr-bar"><b>{{ __('Playlist') }}</b></header>
            <div class="hr-pane hr-sidepane">
            <form class="hr-add" x-show="dj || submissions" x-on:submit.prevent="submit()">
                <b x-text="dj ? @js(__('Add to the radio')) : @js(__('Suggest a station or track'))"></b>
                <input class="hr-input" type="url" x-model="form.link" required maxlength="300" placeholder="{{ __('SoundCloud link or station stream link') }}" aria-label="{{ __('Link') }}">
                <div class="hr-row2">
                    <input class="hr-input" type="text" x-model="form.name" maxlength="60" placeholder="{{ __('Station name (optional)') }}" aria-label="{{ __('Station name') }}">
                    <input class="hr-input" type="text" x-model="form.genre" maxlength="40" placeholder="{{ __('Genre (optional)') }}" aria-label="{{ __('Genre') }}">
                </div>
                <button type="submit" class="hr-btn hr-primary" :disabled="busy" x-text="busy ? @js(__('Checking the link...')) : (dj ? @js(__('Add it')) : @js(__('Send it to the DJs')))"></button>
                <small class="hr-muted" x-show="!dj">{{ __('A DJ listens to every suggestion before it goes on the radio.') }}</small>
                <small class="hr-msg" x-show="message" x-text="message" :class="error && 'bad'"></small>
            </form>
            <p class="hr-note" x-show="!dj && !submissions">{{ __('Suggestions are closed right now.') }}</p>

            <div class="hr-tabs" role="tablist">
                <button type="button" :class="tab === 'all' && 'on'" x-on:click="tab = 'all'">{{ __('All') }}</button>
                <button type="button" :class="tab === 'station' && 'on'" x-on:click="tab = 'station'">{{ __('Stations') }}</button>
                <button type="button" :class="tab === 'track' && 'on'" x-on:click="tab = 'track'">{{ __('Tracks') }}</button>
                <button type="button" x-show="dj" :class="tab === 'review' && 'on'" x-on:click="tab = 'review'">{{ __('To review') }} <span class="hr-count" x-show="pending.length" x-text="pending.length"></span></button>
                <button type="button" x-show="mine.length" :class="tab === 'mine' && 'on'" x-on:click="tab = 'mine'">{{ __('Mine') }}</button>
            </div>
            <input class="hr-input hr-search" type="search" x-model="search" x-show="['all', 'station', 'track'].includes(tab)" placeholder="{{ __('Search') }}" aria-label="{{ __('Search the radio') }}">

            <div class="hr-list" role="list">
                <template x-for="entry in shown()" :key="entry.id">
                    <div class="hr-item" role="listitem" :class="now && now.id === entry.id && 'on'">
                        <template x-if="entry.art"><img :src="entry.art" alt="" width="40" height="40" loading="lazy"></template>
                        <template x-if="!entry.art"><span class="hr-thumb" x-text="entry.kind === 'station' ? 'FM' : '♪'"></span></template>
                        <button type="button" class="hr-pick" x-on:click="entry.status === 'approved' ? pick(entry) : preview(entry)">
                            <b><span class="hr-live" x-show="onAir && onAir.id === entry.id">● </span><span x-text="entry.title"></span></b>
                            <small class="hr-muted" x-text="describe(entry)"></small>
                        </button>
                        <template x-if="tab === 'review'">
                            <span class="hr-acts">
                                <button type="button" class="hr-ok" title="{{ __('Approve') }}" aria-label="{{ __('Approve') }}" x-on:click="review(entry, 'approve')">✓</button>
                                <button type="button" class="hr-x" title="{{ __('Reject') }}" aria-label="{{ __('Reject') }}" x-on:click="review(entry, 'reject')">✕</button>
                            </span>
                        </template>
                        <template x-if="tab !== 'review' && (dj || (entry.mine && entry.status !== 'approved'))">
                            <button type="button" class="hr-x" title="{{ __('Remove') }}" :aria-label="@js(__('Remove')) + ' ' + entry.title" x-on:click="remove(entry)">✕</button>
                        </template>
                    </div>
                </template>
                <p class="hr-note" x-show="!shown().length" x-text="tab === 'review' ? @js(__('Nothing waiting. Nice.')) : @js(__('Nothing here yet.'))"></p>
            </div>
            </div>
        </aside>
    </div>

    @push('javascript')
        <style>
            .hr { --ink: #1d2b36; --frame: #31495b; --edge: #14202a; --paper: #e9e5d8; --paper-2: #f6f3ea; --soft: #cfcabb; --blue: #2f86b8; --blue-d: #1d5f86; --green: #6cc22f; --green-d: #3f8a14; --red: #e0392b; --red-d: #9d2218; --lcd: #0b1d14; --lcd-on: #8dff8a; --lcd-dim: #4f9d55; display: grid; grid-template-columns: minmax(0, 1fr) 340px; gap: 16px; align-items: start; color: var(--ink); font-family: Ubuntu, "Trebuchet MS", "Segoe UI", sans-serif; }
            .hr * { box-sizing: border-box; }
            .hr-win { background: var(--frame); border: 2px solid var(--edge); border-radius: 6px; box-shadow: inset 0 0 0 2px #4d6a80, 3px 3px 0 rgba(0, 0, 0, .28); min-width: 0; overflow: hidden; }
            .hr-bar { display: flex; align-items: center; gap: 8px; padding: 6px 10px; background: var(--blue); border-bottom: 2px solid var(--edge); color: #fff; text-shadow: 0 1px 0 var(--edge); box-shadow: inset 0 2px 0 rgba(255, 255, 255, .28); }
            .hr-bar b { font-size: 14px; letter-spacing: .04em; text-transform: uppercase; flex: 1; }
            .hr-logo { flex: none; }
            .hr-lamp { display: inline-flex; align-items: center; gap: 6px; font-size: 11px; font-weight: 700; letter-spacing: .08em; padding: 2px 8px; background: var(--edge); border-radius: 3px; color: #7b8a96; text-shadow: none; }
            .hr-lamp i { width: 9px; height: 9px; background: #5b2a26; border: 1px solid #000; display: block; }
            .hr-lamp.lit { color: #fff; }
            .hr-lamp.lit i { background: var(--red); box-shadow: 0 0 6px var(--red); animation: hr-blink 1.2s steps(1) infinite; }
            @keyframes hr-blink { 50% { opacity: .35; } }
            .hr-pane { margin: 8px; padding: 12px; background: var(--paper); border: 2px solid var(--edge); border-radius: 4px; box-shadow: inset 0 2px 0 #fff; display: grid; gap: 12px; }
            .hr-lcd { display: flex; align-items: stretch; gap: 14px; min-height: 124px; padding: 12px 14px; background: var(--lcd); border: 2px solid var(--edge); border-radius: 4px; box-shadow: inset 0 3px 0 rgba(0, 0, 0, .55), inset 0 0 0 2px #17402b; color: var(--lcd-on); font-family: "Courier New", ui-monospace, Menlo, monospace; text-shadow: 0 0 5px rgba(141, 255, 138, .45); }
            .hr-art { width: 100px; height: 100px; object-fit: cover; flex: none; align-self: center; border: 2px solid var(--lcd-dim); image-rendering: auto; }
            .hr-readout { flex: 1; min-width: 0; display: grid; align-content: center; gap: 4px; }
            .hr-readout small { color: var(--lcd-dim); font-size: 11.5px; font-weight: 700; letter-spacing: .12em; text-transform: uppercase; }
            .hr-readout span { color: var(--lcd-dim); font-size: 13px; overflow-wrap: anywhere; }
            .hr-big { font-size: 22px; line-height: 1.15; text-transform: uppercase; overflow-wrap: anywhere; }
            .hr-eq { display: flex; align-items: flex-end; gap: 3px; width: 112px; height: 92px; align-self: center; flex: none; }
            .hr-eq i { flex: 1; height: 14%; background: repeating-linear-gradient(to top, var(--lcd-on) 0 5px, transparent 5px 7px); opacity: .55; }
            .hr-lcd.live .hr-eq i { opacity: 1; animation: hr-eq 1s steps(1, end) infinite; animation-delay: calc(var(--d) * -.09s); }
            @keyframes hr-eq { 0% { height: 18%; } 15% { height: 78%; } 30% { height: 42%; } 45% { height: 100%; } 60% { height: 58%; } 80% { height: 30%; } 100% { height: 18%; } }
            .hr-stage:empty { display: none; }
            .hr-stage { border: 2px solid var(--edge); border-radius: 4px; background: #000; overflow: hidden; box-shadow: 2px 2px 0 rgba(0, 0, 0, .2); }
            .hr-stage:has(> audio) { display: none !important; }
            .hr-frame { display: block; width: 100%; border: 0; }
            .hr-deck { display: flex; align-items: center; gap: 14px; flex-wrap: wrap; }
            .hr-ctl { display: flex; gap: 8px; flex-wrap: wrap; align-items: center; }
            .hr-ctl .hr-ctl { margin: 0; }
            .hr-more { padding-top: 10px; border-top: 2px dotted var(--soft); }
            .hr-btn { display: inline-flex; align-items: center; justify-content: center; gap: 4px; background: var(--paper-2); color: var(--ink); border: 2px solid var(--edge); border-radius: 4px; padding: 5px 12px; font: inherit; font-size: 13px; font-weight: 700; line-height: 1.2; cursor: pointer; text-decoration: none; box-shadow: inset 0 -3px 0 #b9b4a3, inset 0 2px 0 #fff; }
            .hr-btn:hover { background: #fff; }
            .hr-btn:active { transform: translateY(1px); box-shadow: inset 0 2px 0 #b9b4a3; }
            .hr-btn:disabled { opacity: .5; cursor: default; transform: none; }
            .hr-btn:focus-visible, .hr-tabs button:focus-visible, .hr-pick:focus-visible { outline: 2px solid var(--blue); outline-offset: 2px; }
            .hr-sq { width: 40px; height: 36px; padding: 0; font-size: 15px; }
            .hr-primary { background: var(--green); color: #fff; text-shadow: 0 1px 0 var(--green-d); box-shadow: inset 0 -3px 0 var(--green-d), inset 0 2px 0 rgba(255, 255, 255, .4); }
            .hr-primary:hover { background: #7bd13c; }
            .hr-blue, .hr-btn.on { background: var(--blue); color: #fff; text-shadow: 0 1px 0 var(--blue-d); box-shadow: inset 0 -3px 0 var(--blue-d), inset 0 2px 0 rgba(255, 255, 255, .35); }
            .hr-blue:hover, .hr-btn.on:hover { background: #3a95c9; }
            .hr-red { background: var(--red); color: #fff; text-shadow: 0 1px 0 var(--red-d); box-shadow: inset 0 -3px 0 var(--red-d), inset 0 2px 0 rgba(255, 255, 255, .3); }
            .hr-red:hover { background: #ec4b3d; }
            .hr-vol { display: flex; align-items: center; gap: 8px; flex: 1; min-width: 150px; font-size: 11.5px; font-weight: 700; letter-spacing: .1em; color: var(--ink); }
            .hr-vol input { flex: 1; min-width: 0; accent-color: var(--blue); height: 8px; cursor: pointer; }
            .hr-sidepane { display: flex; flex-direction: column; gap: 10px; max-height: 70vh; padding: 10px; }
            .hr-add { display: grid; gap: 6px; padding-bottom: 10px; border-bottom: 2px dotted var(--soft); }
            .hr-add > b { font-size: 13px; text-transform: uppercase; letter-spacing: .04em; }
            .hr-row2 { display: grid; grid-template-columns: 1fr 1fr; gap: 6px; }
            .hr-input { width: 100%; min-width: 0; background: #fff; color: var(--ink); border: 2px solid var(--edge); border-radius: 4px; padding: 6px 9px; font: inherit; font-size: 13px; box-shadow: inset 0 2px 0 rgba(0, 0, 0, .12); }
            .hr-input:focus { outline: 2px solid var(--blue); outline-offset: 0; }
            .hr-input::placeholder { color: #75808a; }
            .hr-muted { color: #5c6b77; }
            .hr-msg { color: #1f7a3d; font-weight: 700; font-size: 12.5px; } .hr-msg.bad { color: var(--red-d); }
            .hr-note { color: #5c6b77; font-size: 13px; padding: 8px 4px; }
            .hr-tabs { display: flex; gap: 3px; flex-wrap: wrap; margin-bottom: -10px; position: relative; z-index: 1; }
            .hr-tabs button { background: var(--soft); border: 2px solid var(--edge); border-bottom: 0; color: var(--ink); border-radius: 5px 5px 0 0; padding: 4px 10px 5px; font: inherit; font-size: 12.5px; font-weight: 700; cursor: pointer; }
            .hr-tabs button:hover { background: #ded9ca; }
            .hr-tabs button.on { background: var(--paper-2); padding-bottom: 7px; margin-bottom: -2px; }
            .hr-count { background: var(--red); color: #fff; border-radius: 3px; padding: 0 5px; font-size: 11px; margin-left: 2px; }
            .hr-search { margin-top: 10px; }
            .hr-list { overflow-y: auto; flex: 1; min-height: 120px; background: var(--paper-2); border: 2px solid var(--edge); border-radius: 0 4px 4px 4px; box-shadow: inset 0 2px 0 rgba(0, 0, 0, .1); }
            .hr-item { display: flex; align-items: center; gap: 8px; padding: 5px 6px; border-bottom: 1px solid var(--soft); border-left: 4px solid transparent; }
            .hr-item:nth-child(even) { background: #efebde; }
            .hr-item:hover { background: #dcedf6; }
            .hr-item.on { background: #bfe0f1; border-left-color: var(--blue); }
            .hr-item img, .hr-thumb { width: 38px; height: 38px; border: 2px solid var(--edge); border-radius: 3px; object-fit: cover; flex: none; background: var(--frame); color: var(--lcd-on); display: grid; place-items: center; font: 700 12px "Courier New", ui-monospace, monospace; }
            .hr-pick { flex: 1; min-width: 0; text-align: left; background: none; border: 0; color: var(--ink); font: inherit; cursor: pointer; display: grid; padding: 0; }
            .hr-pick b, .hr-pick small { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; font-size: 13px; }
            .hr-pick small { font-size: 11.5px; font-weight: 400; }
            .hr-live { color: var(--red); font-weight: 700; }
            .hr-acts { display: flex; gap: 3px; }
            .hr-x, .hr-ok { background: var(--paper-2); border: 2px solid var(--edge); color: var(--ink); cursor: pointer; padding: 1px 6px; border-radius: 3px; font-size: 12px; font-weight: 700; box-shadow: inset 0 -2px 0 #b9b4a3; }
            .hr-x:hover { background: var(--red); color: #fff; box-shadow: inset 0 -2px 0 var(--red-d); }
            .hr-ok:hover { background: var(--green); color: #fff; box-shadow: inset 0 -2px 0 var(--green-d); }
            @media (max-width: 820px) { .hr { grid-template-columns: 1fr; } .hr-sidepane { max-height: none; } .hr-lcd { flex-wrap: wrap; } .hr-eq { width: 100%; height: 40px; } }
            @media (prefers-reduced-motion: reduce) { .hr-lcd.live .hr-eq i, .hr-lamp.lit i { animation: none; } }
        </style>
        <script>
            (() => {
                const WIDGET = 'https://w.soundcloud.com';
                const KEY = 'hotel-radio';
                const read = () => { try { return JSON.parse(localStorage.getItem(KEY) || '{}'); } catch { return {}; } };
                const write = (value) => { try { localStorage.setItem(KEY, JSON.stringify(value)); } catch { /* fine without it */ } };
                const kinds = { station: @js(__('Station')), track: @js(__('Track')), set: @js(__('Set')), pending: @js(__('Waiting for a DJ')), rejected: @js(__('Not this time')), by: @js(__('from')),
                    standby: @js(__('Standby')), pick: @js(__('Pick something to play')), quiet: @js(__('The radio is quiet')), choose: @js(__('Choose a station or a track from the list.')), live: @js(__('On air')), stationTag: @js(__('Live station')), nowTag: @js(__('Now playing')), onNow: @js(__('On air now')) };

                const radio = (urls) => ({
                    dj: false, submissions: false, onAir: null, entries: [], pending: [], mine: [],
                    now: null, following: true, heard: false, auto: read().auto !== false,
                    playing: false, volume: read().volume ?? 0.8, audio: null,
                    tab: 'all', search: '', busy: false, message: '', error: false,
                    form: { link: '', name: '', genre: '' },
                    frame: null,
                    box: null,

                    get live() { return !!(this.following && this.onAir && this.now && this.onAir.id === this.now.id); },
                    screen() {
                        const e = this.now || this.onAir;
                        if (!e) {
                            return { tag: kinds.standby, title: this.entries.length ? kinds.pick : kinds.quiet, sub: kinds.choose };
                        }
                        const tag = this.live ? kinds.live : (this.now ? (this.now.kind === 'station' ? kinds.stationTag : kinds.nowTag) : kinds.onNow);
                        return { tag, title: e.title, sub: e.subtitle || '' };
                    },
                    start() {
                        this.box = this.$el;
                        this.listen = (event) => this.widget(event);
                        window.addEventListener('message', this.listen);
                        this.load();
                        this.timer = setInterval(() => { if (!document.hidden) this.load(); }, 15000);
                    },
                    async call(url, method = 'GET', body = null) {
                        const res = await fetch(url, {
                            method, credentials: 'same-origin',
                            headers: { Accept: 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '' },
                            body: body ? JSON.stringify(body) : null,
                        });
                        const data = await res.json().catch(() => ({}));
                        if (!res.ok) throw new Error(data.errors ? Object.values(data.errors)[0][0] : (data.message || @js(__('Something went wrong. Try again.'))));
                        return data;
                    },
                    async load() {
                        let data;
                        try { data = await this.call(urls.feed); } catch { return; }
                        const before = this.onAir?.id ?? null;
                        Object.assign(this, { dj: data.dj, submissions: data.submissions, onAir: data.on_air, entries: data.entries, pending: data.pending, mine: data.mine });
                        if (this.heard && this.following && (data.on_air?.id ?? null) !== before) {
                            data.on_air ? this.play(data.on_air) : this.stop();
                        }
                        if (this.now && this.now.status === 'approved' && !this.entries.some((e) => e.id === this.now.id)) this.stop();
                    },
                    shown() {
                        if (this.tab === 'review') return this.pending;
                        if (this.tab === 'mine') return this.mine;
                        const q = this.search.trim().toLowerCase();
                        return this.entries.filter((e) => (this.tab === 'all' || e.kind === this.tab)
                            && (!q || `${e.title} ${e.subtitle}`.toLowerCase().includes(q)));
                    },
                    describe(e) {
                        const parts = [e.subtitle || (e.kind === 'station' ? kinds.station : (e.set ? kinds.set : kinds.track))];
                        if (e.status === 'pending') parts.push(kinds.pending);
                        if (e.status === 'rejected') parts.push(kinds.rejected);
                        if (e.by) parts.push(`${kinds.by} ${e.by}`);
                        return parts.join(' · ');
                    },
                    tuneIn() { this.following = true; this.onAir ? this.play(this.onAir) : this.stop(); },
                    pick(entry) { this.following = !!(this.onAir && this.onAir.id === entry.id); this.play(entry); },
                    preview(entry) { this.following = false; this.play(entry); },
                    play(entry) {
                        this.heard = true;
                        this.now = entry;
                        this.$nextTick(() => {
                            const stage = this.box.querySelector('.hr-stage');
                            if (!stage) return;
                            stage.replaceChildren();
                            this.frame = null;
                            if (entry.kind === 'station') {
                                const audio = document.createElement('audio');
                                Object.assign(audio, { autoplay: true, src: entry.stream, volume: this.volume });
                                audio.addEventListener('play', () => { this.playing = true; });
                                audio.addEventListener('pause', () => { this.playing = false; });
                                this.audio = audio;
                                stage.append(audio);
                                audio.play().catch(() => {});
                                return;
                            }
                            this.audio = null;
                            this.playing = true;
                            const frame = document.createElement('iframe');
                            frame.className = 'hr-frame';
                            frame.allow = 'autoplay; encrypted-media';
                            frame.title = entry.title;
                            frame.height = entry.set ? '380' : '166';
                            frame.src = `${WIDGET}/player/?url=${encodeURIComponent(entry.stream)}&auto_play=true&color=%232f86b8&hide_related=true&show_comments=false&show_reposts=false&visual=false`;
                            frame.addEventListener('load', () => this.subscribe());
                            this.frame = frame;
                            stage.append(frame);
                        });
                    },
                    stop() {
                        if (this.audio) this.audio.pause();
                        this.audio = null;
                        this.playing = false;
                        this.now = null;
                        this.frame = null;
                        this.box.querySelector('.hr-stage')?.replaceChildren();
                    },
                    send(message) {
                        try { this.frame.contentWindow.postMessage(JSON.stringify(message), WIDGET); } catch { /* the ready message covers it */ }
                    },
                    subscribe() {
                        if (!this.frame) return;
                        ['finish', 'play', 'pause'].forEach((value) => this.send({ method: 'addEventListener', value }));
                        this.send({ method: 'setVolume', value: Math.round(this.volume * 100) });
                    },
                    toggle() {
                        if (this.audio) {
                            if (this.audio.paused) { this.audio.src = this.now.stream; this.audio.play().catch(() => {}); } else this.audio.pause();
                        } else if (this.frame) this.send({ method: 'toggle' });
                    },
                    setVolume() {
                        write({ ...read(), volume: this.volume });
                        if (this.audio) this.audio.volume = this.volume;
                        else if (this.frame) this.send({ method: 'setVolume', value: Math.round(this.volume * 100) });
                    },
                    widget(event) {
                        if (!this.frame || event.source !== this.frame.contentWindow || event.origin !== WIDGET) return;
                        let data = event.data;
                        try { if (typeof data === 'string') data = JSON.parse(data); } catch { return; }
                        if (data?.method === 'ready') this.subscribe();
                        if (data?.method === 'play') this.playing = true;
                        if (data?.method === 'pause') this.playing = false;
                        if (data?.method === 'finish') this.playing = false;
                        if (data?.method === 'finish' && this.auto && this.now && !this.now.set) this.step(1);
                    },
                    step(direction) {
                        const tracks = this.entries.filter((e) => e.kind === 'track');
                        if (!tracks.length) return;
                        const at = tracks.findIndex((e) => e.id === this.now?.id);
                        this.following = false;
                        this.play(tracks[(at + direction + tracks.length) % tracks.length]);
                    },
                    remember() { write({ ...read(), auto: this.auto }); },
                    async submit() {
                        this.busy = true; this.message = ''; this.error = false;
                        try {
                            const data = await this.call(urls.store, 'POST', this.form);
                            this.form = { link: '', name: '', genre: '' };
                            this.message = data.message;
                            await this.load();
                        } catch (e) { this.error = true; this.message = e.message; } finally { this.busy = false; }
                    },
                    async act(url, method, body = null) {
                        try { await this.call(url, method, body); await this.load(); } catch (e) { this.error = true; this.message = e.message; }
                    },
                    review(entry, verdict) { this.act(`${urls.base}/${entry.id}/${verdict}`, 'POST'); },
                    remove(entry) { if (confirm(@js(__('Remove')) + ` "${entry.title}"?`)) this.act(`${urls.base}/${entry.id}`, 'DELETE'); },
                    async putOnAir(id) {
                        await this.act(urls.onAir, 'POST', { id });
                        this.following = true;
                    },
                });

                if (window.Alpine) window.Alpine.data('hotelRadio', radio);
                else document.addEventListener('alpine:init', () => window.Alpine.data('hotelRadio', radio));
            })();
        </script>
    @endpush
</x-app-layout>

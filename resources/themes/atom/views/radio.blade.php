<x-app-layout>
    @push('title', __('Radio'))

    <div class="col-span-12 hr" x-data="hotelRadio(@js([
        'feed' => route('radio.feed', absolute: false),
        'store' => route('radio.store', absolute: false),
        'onAir' => route('radio.on-air', absolute: false),
        'base' => rtrim(route('radio.index', absolute: false), '/'),
    ]))" x-init="start()">
        <section class="hr-main">
            <template x-if="!now">
                <div class="hr-empty">
                    <div class="hr-icon" aria-hidden="true">📻</div>
                    <template x-if="onAir">
                        <div class="hr-stack">
                            <small class="hr-muted">{{ __('ON AIR NOW') }}</small>
                            <b class="hr-big" x-text="onAir.title"></b>
                            <span class="hr-muted" x-text="onAir.subtitle"></span>
                            <button type="button" class="hr-btn hr-primary" x-on:click="tuneIn()">{{ __('Tune in') }}</button>
                        </div>
                    </template>
                    <template x-if="!onAir">
                        <div class="hr-stack">
                            <b class="hr-big" x-text="entries.length ? @js(__('Pick something to play')) : @js(__('The radio is quiet'))"></b>
                            <span class="hr-muted">{{ __('Choose a station or a track from the list.') }}</span>
                        </div>
                    </template>
                </div>
            </template>

            <template x-if="now">
                <div>
                    <div class="hr-now">
                        <template x-if="now.art"><img class="hr-art" :src="now.art" alt=""></template>
                        <template x-if="!now.art"><div class="hr-art hr-noart" x-text="now.kind === 'station' ? '📡' : '♪'"></div></template>
                        <div class="hr-stack">
                            <small class="hr-muted">
                                <span x-show="following && onAir && onAir.id === now.id" class="hr-live">● {{ __('ON AIR') }}</span>
                                <span x-show="!(following && onAir && onAir.id === now.id)" x-text="now.kind === 'station' ? @js(__('LIVE STATION')) : @js(__('NOW PLAYING'))"></span>
                            </small>
                            <b class="hr-big" x-text="now.title"></b>
                            <span class="hr-muted" x-text="now.subtitle"></span>
                            <small class="hr-muted" x-show="now.by" x-text="@js(__('Added by')) + ' ' + now.by"></small>
                        </div>
                    </div>

                    <div class="hr-stage"></div>

                    <div class="hr-ctl">
                        <button type="button" class="hr-btn" x-show="now.kind === 'track'" x-on:click="step(-1)" title="{{ __('Previous') }}">⏮</button>
                        <button type="button" class="hr-btn" x-show="now.kind === 'track'" x-on:click="step(1)" title="{{ __('Next') }}">⏭</button>
                        <button type="button" class="hr-btn" x-show="now.kind === 'track'" :class="auto && 'on'" x-on:click="auto = !auto; remember()">⟳ {{ __('Auto-next') }}</button>
                        <button type="button" class="hr-btn" x-show="onAir && !(following && onAir.id === now.id)" x-on:click="tuneIn()">● {{ __('Back to on air') }}</button>
                        <template x-if="dj">
                            <span class="hr-ctl">
                                <button type="button" class="hr-btn hr-primary" x-show="!onAir || onAir.id !== now.id" x-on:click="putOnAir(now.id)">{{ __('Put on air') }}</button>
                                <button type="button" class="hr-btn" x-show="onAir && onAir.id === now.id" x-on:click="putOnAir(null)">{{ __('Take off air') }}</button>
                            </span>
                        </template>
                        <a class="hr-btn" :href="now.url" target="_blank" rel="noopener noreferrer" x-show="now.kind === 'track'">{{ __('Open on SoundCloud') }} ↗</a>
                        <button type="button" class="hr-btn" x-on:click="stop()">■ {{ __('Stop') }}</button>
                    </div>
                </div>
            </template>
        </section>

        <aside class="hr-side">
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
                        <template x-if="!entry.art"><span class="hr-thumb" x-text="entry.kind === 'station' ? '📡' : '♪'"></span></template>
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
        </aside>
    </div>

    @push('javascript')
        <style>
            .hr { display: grid; grid-template-columns: minmax(0, 1fr) 340px; gap: 16px; color: var(--text, inherit); }
            .hr-main, .hr-side { background: var(--panel, rgba(127, 127, 127, .08)); border: 1px solid var(--line, rgba(127, 127, 127, .2)); border-radius: var(--radius, 14px); padding: 18px; min-width: 0; }
            .hr-side { display: flex; flex-direction: column; gap: 10px; padding: 12px; max-height: 78vh; }
            .hr-muted { color: var(--muted, #8396ad); }
            .hr-stack { display: grid; gap: 4px; justify-items: start; min-width: 0; }
            .hr-empty { text-align: center; padding: 50px 10px; display: grid; justify-items: center; gap: 8px; }
            .hr-empty .hr-stack { justify-items: center; }
            .hr-icon { font-size: 56px; }
            .hr-big { font-size: 20px; color: var(--white, inherit); overflow-wrap: anywhere; }
            .hr-now { display: flex; gap: 16px; align-items: center; margin-bottom: 14px; }
            .hr-art { width: 112px; height: 112px; border-radius: 12px; object-fit: cover; flex: none; box-shadow: 0 8px 24px rgba(0, 0, 0, .35); }
            .hr-noart { display: grid; place-items: center; font-size: 40px; background: var(--soft, rgba(127, 127, 127, .15)); }
            .hr-live { color: var(--bad, #ff6b6b); font-weight: 700; }
            .hr-frame { display: block; width: 100%; border: 0; border-radius: 10px; }
            .hr-audio { width: 100%; }
            .hr-ctl { display: flex; gap: 8px; flex-wrap: wrap; margin-top: 12px; align-items: center; }
            .hr-ctl .hr-ctl { margin: 0; }
            .hr-btn { background: var(--panel-2, rgba(127, 127, 127, .12)); border: 1px solid var(--line, rgba(127, 127, 127, .25)); color: var(--text, inherit); border-radius: 9px; padding: 6px 12px; font: inherit; font-size: 13px; cursor: pointer; text-decoration: none; }
            .hr-btn:hover { border-color: var(--accent, #4da3ff); }
            .hr-btn.on, .hr-primary { background: var(--accent, #4da3ff); border-color: var(--accent, #4da3ff); color: var(--on-accent, #06121f); font-weight: 600; }
            .hr-btn:disabled { opacity: .6; cursor: wait; }
            .hr-add { display: grid; gap: 6px; padding-bottom: 10px; border-bottom: 1px solid var(--line, rgba(127, 127, 127, .2)); }
            .hr-row2 { display: grid; grid-template-columns: 1fr 1fr; gap: 6px; }
            .hr-input { width: 100%; min-width: 0; background: var(--input-bg, transparent); color: var(--text, inherit); border: 1px solid var(--line, rgba(127, 127, 127, .3)); border-radius: 8px; padding: 7px 10px; font: inherit; font-size: 13px; }
            .hr-msg { color: var(--good, #3ddc97); } .hr-msg.bad { color: var(--bad, #ff6b6b); }
            .hr-note { color: var(--muted, #8396ad); font-size: 13px; padding: 8px 4px; }
            .hr-tabs { display: flex; gap: 4px; flex-wrap: wrap; }
            .hr-tabs button { background: none; border: 1px solid transparent; color: var(--muted, #8396ad); border-radius: 999px; padding: 4px 10px; font: inherit; font-size: 12.5px; cursor: pointer; }
            .hr-tabs button.on { border-color: var(--accent, #4da3ff); color: var(--white, inherit); background: var(--hover-row, rgba(77, 163, 255, .08)); }
            .hr-count { background: var(--bad, #ff6b6b); color: #fff; border-radius: 999px; padding: 0 6px; font-size: 11px; margin-left: 2px; }
            .hr-list { overflow-y: auto; flex: 1; min-height: 120px; }
            .hr-item { display: flex; align-items: center; gap: 8px; padding: 5px; border-radius: 8px; border: 1px solid transparent; }
            .hr-item:hover { background: var(--hover-row, rgba(77, 163, 255, .08)); }
            .hr-item.on { border-color: var(--accent, #4da3ff); }
            .hr-item img, .hr-thumb { width: 40px; height: 40px; border-radius: 6px; object-fit: cover; flex: none; background: var(--soft, rgba(127, 127, 127, .15)); display: grid; place-items: center; }
            .hr-pick { flex: 1; min-width: 0; text-align: left; background: none; border: 0; color: inherit; font: inherit; cursor: pointer; display: grid; padding: 0; }
            .hr-pick b, .hr-pick small { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; font-size: 13px; }
            .hr-pick small { font-size: 11.5px; }
            .hr-acts { display: flex; gap: 2px; }
            .hr-x, .hr-ok { background: none; border: 0; color: var(--muted, #8396ad); cursor: pointer; padding: 4px 7px; border-radius: 6px; }
            .hr-x:hover { color: #fff; background: var(--bad, #ff6b6b); }
            .hr-ok:hover { color: #06121f; background: var(--good, #3ddc97); }
            @media (max-width: 820px) { .hr { grid-template-columns: 1fr; } .hr-side { max-height: none; } }
        </style>
        <script>
            (() => {
                const WIDGET = 'https://w.soundcloud.com';
                const KEY = 'hotel-radio';
                const read = () => { try { return JSON.parse(localStorage.getItem(KEY) || '{}'); } catch { return {}; } };
                const write = (value) => { try { localStorage.setItem(KEY, JSON.stringify(value)); } catch { /* fine without it */ } };
                const kinds = { station: @js(__('Station')), track: @js(__('Track')), set: @js(__('Set')), pending: @js(__('Waiting for a DJ')), rejected: @js(__('Not this time')), by: @js(__('from')) };

                const radio = (urls) => ({
                    dj: false, submissions: false, onAir: null, entries: [], pending: [], mine: [],
                    now: null, following: true, heard: false, auto: read().auto !== false,
                    tab: 'all', search: '', busy: false, message: '', error: false,
                    form: { link: '', name: '', genre: '' },
                    frame: null,
                    box: null,

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
                                Object.assign(audio, { className: 'hr-audio', controls: true, autoplay: true, src: entry.stream, volume: read().volume ?? 0.8 });
                                audio.addEventListener('volumechange', () => write({ ...read(), volume: audio.volume }));
                                stage.append(audio);
                                audio.play().catch(() => {});
                                return;
                            }
                            const frame = document.createElement('iframe');
                            frame.className = 'hr-frame';
                            frame.allow = 'autoplay; encrypted-media';
                            frame.title = entry.title;
                            frame.height = entry.set ? '380' : '166';
                            frame.src = `${WIDGET}/player/?url=${encodeURIComponent(entry.stream)}&auto_play=true&color=%234da3ff&hide_related=true&show_comments=false&show_reposts=false&visual=false`;
                            frame.addEventListener('load', () => this.subscribe());
                            this.frame = frame;
                            stage.append(frame);
                        });
                    },
                    stop() {
                        this.now = null;
                        this.frame = null;
                        this.box.querySelector('.hr-stage')?.replaceChildren();
                    },
                    subscribe() {
                        try { this.frame.contentWindow.postMessage(JSON.stringify({ method: 'addEventListener', value: 'finish' }), WIDGET); } catch { /* the ready message covers it */ }
                    },
                    widget(event) {
                        if (!this.frame || event.source !== this.frame.contentWindow || event.origin !== WIDGET) return;
                        let data = event.data;
                        try { if (typeof data === 'string') data = JSON.parse(data); } catch { return; }
                        if (data?.method === 'ready') this.subscribe();
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

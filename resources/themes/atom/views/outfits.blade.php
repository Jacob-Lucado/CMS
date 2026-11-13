<x-app-layout>
    @push('title', __('Outfit of the Week'))

    <div class="col-span-12 ow" x-data="outfitWeek(@js($config))" x-init="start()">
        <section class="ow-card ow-hero">
            <div>
                <b class="ow-title">👕 {{ __('Outfit of the Week') }}</b>
                <p class="ow-muted">{{ __('Enter the outfit you have on in the hotel and vote for your favorites. The entry with the most votes when the week ends wins.') }}</p>
                <p class="ow-small">{{ __('Voting closes in') }} <b x-text="countdown"></b></p>
            </div>
            <div class="ow-row">
                <button type="button" class="ow-btn ow-primary" :disabled="busy" x-on:click="enter()" x-text="mine ? @js(__('Update my entry')) : @js(__('Enter my outfit'))"></button>
                <button type="button" class="ow-btn" x-show="mine" x-on:click="shareEntry()">🔁 {{ __('Share my entry') }}</button>
            </div>
        </section>

        <section class="ow-card">
            <b>{{ __("This week's entries") }}</b>
            <div class="ow-grid">
                <template x-for="(e, i) in entries" :key="e.id">
                    <article class="ow-entry" :class="e.mine && 'mine'">
                        <span class="ow-place" x-show="i < 3 && e.votes > 0" x-text="['🥇', '🥈', '🥉'][i]"></span>
                        <img :src="figure(e.look)" alt="" loading="lazy">
                        <a class="ow-name" :href="e.author?.home" x-text="e.author?.name"></a>
                        <button type="button" class="ow-vote" :class="e.voted && 'on'" :disabled="e.mine" x-on:click="vote(e)">
                            <span x-text="e.voted ? '❤️' : '🤍'"></span> <span x-text="e.votes"></span>
                        </button>
                    </article>
                </template>
            </div>
            <p class="ow-muted ow-empty" x-show="!entries.length">{{ __('No entries yet this week. Be the first!') }}</p>
        </section>

        <section class="ow-card" x-show="winners.length">
            <b>🏆 {{ __('Past winners') }}</b>
            <div class="ow-grid ow-small-grid">
                <template x-for="w in winners" :key="w.id">
                    <article class="ow-entry">
                        <img :src="figure(w.look)" alt="" loading="lazy">
                        <a class="ow-name" :href="w.author?.home" x-text="w.author?.name"></a>
                        <span class="ow-muted ow-small" x-text="w.week"></span>
                        <span class="ow-small">❤️ <span x-text="w.votes"></span></span>
                    </article>
                </template>
            </div>
        </section>

        <section class="ow-card ow-imager">
            <b>🎨 {{ __('Avatar imager') }}</b>
            <p class="ow-muted">{{ __('Pose your avatar, then share it to the social feed or copy the image link.') }}</p>
            <div class="ow-imagerbox">
                <div class="ow-stage"><img :src="pose()" alt=""></div>
                <div class="ow-controls">
                    <label>{{ __('Body') }}
                        <span class="ow-row"><button type="button" class="ow-btn" x-on:click="opts.direction = (opts.direction + 7) % 8">⟲</button><button type="button" class="ow-btn" x-on:click="opts.direction = (opts.direction + 1) % 8">⟳</button></span>
                    </label>
                    <label>{{ __('Head') }}
                        <span class="ow-row"><button type="button" class="ow-btn" x-on:click="opts.head_direction = (opts.head_direction + 7) % 8">⟲</button><button type="button" class="ow-btn" x-on:click="opts.head_direction = (opts.head_direction + 1) % 8">⟳</button></span>
                    </label>
                    <label>{{ __('Action') }}
                        <select class="ow-input" x-model="opts.action">
                            <option value="std">{{ __('Stand') }}</option><option value="wlk">{{ __('Walk') }}</option><option value="sit">{{ __('Sit') }}</option>
                            <option value="lay">{{ __('Lay') }}</option><option value="wav">{{ __('Wave') }}</option><option value="spk">{{ __('Talk') }}</option>
                        </select>
                    </label>
                    <label>{{ __('Face') }}
                        <select class="ow-input" x-model="opts.gesture">
                            <option value="std">{{ __('Normal') }}</option><option value="sml">{{ __('Smile') }}</option><option value="agr">{{ __('Angry') }}</option>
                            <option value="sad">{{ __('Sad') }}</option><option value="srp">{{ __('Surprised') }}</option><option value="eyb">{{ __('Sleepy') }}</option>
                        </select>
                    </label>
                    <label>{{ __('Size') }}
                        <select class="ow-input" x-model="opts.size"><option value="s">{{ __('Small') }}</option><option value="m">{{ __('Medium') }}</option><option value="l">{{ __('Large') }}</option></select>
                    </label>
                    <label class="ow-check"><input type="checkbox" x-model="headonly"> {{ __('Head only') }}</label>
                    <span class="ow-row">
                        <button type="button" class="ow-btn ow-primary" x-on:click="sharePose()">🔁 {{ __('Share to feed') }}</button>
                        <button type="button" class="ow-btn" x-on:click="copyPose()">🔗 {{ __('Copy link') }}</button>
                    </span>
                </div>
            </div>
        </section>

        <div class="ow-toast" x-show="toast" x-transition x-cloak x-text="toast"></div>
    </div>

    @push('javascript')
        <style>
            [x-cloak] { display: none !important; }
            .ow { display: grid; gap: 14px; }
            .ow-card { background: var(--panel, rgba(127, 127, 127, .08)); border: 1px solid var(--line, rgba(127, 127, 127, .2)); border-radius: var(--radius, 14px); padding: 14px; display: grid; gap: 8px; min-width: 0; }
            .ow-hero { grid-template-columns: 1fr auto; align-items: center; }
            .ow-title { font-size: 18px; }
            .ow-row { display: flex; gap: 6px; align-items: center; flex-wrap: wrap; }
            .ow-muted { color: var(--muted, #8396ad); font-size: 13px; margin: 0; } .ow-small { font-size: 12.5px; margin: 0; }
            .ow-btn { background: var(--panel-2, rgba(127, 127, 127, .12)); border: 1px solid var(--line, rgba(127, 127, 127, .25)); color: var(--text, inherit); border-radius: 9px; padding: 6px 12px; font: inherit; font-size: 13px; cursor: pointer; }
            .ow-primary { background: var(--accent, #4da3ff); border-color: var(--accent, #4da3ff); color: var(--on-accent, #06121f); font-weight: 600; }
            .ow-btn:disabled { opacity: .6; cursor: default; }
            .ow-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(130px, 1fr)); gap: 10px; }
            .ow-small-grid { grid-template-columns: repeat(auto-fill, minmax(110px, 1fr)); }
            .ow-entry { position: relative; display: grid; justify-items: center; gap: 4px; padding: 10px 6px; border-radius: 12px; background: var(--soft, rgba(127, 127, 127, .1)); border: 1px solid transparent; }
            .ow-entry.mine { border-color: var(--accent, #4da3ff); }
            .ow-entry img { height: 130px; image-rendering: pixelated; }
            .ow-place { position: absolute; top: 6px; left: 8px; font-size: 20px; }
            .ow-name { font-weight: 700; color: var(--white, inherit); text-decoration: none; font-size: 13.5px; }
            .ow-vote { background: none; border: 1px solid var(--line, rgba(127, 127, 127, .3)); border-radius: 999px; color: inherit; font: inherit; font-size: 13px; padding: 3px 12px; cursor: pointer; }
            .ow-vote.on { border-color: #e5484d; }
            .ow-vote:disabled { cursor: default; opacity: .7; }
            .ow-empty { text-align: center; padding: 20px; }
            .ow-imagerbox { display: grid; grid-template-columns: 220px 1fr; gap: 14px; align-items: start; }
            .ow-stage { display: grid; place-items: center; min-height: 230px; border-radius: 12px; background: repeating-conic-gradient(rgba(127, 127, 127, .14) 0 25%, transparent 0 50%) 0 0 / 20px 20px; }
            .ow-stage img { image-rendering: pixelated; max-width: 100%; }
            .ow-controls { display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 10px; align-content: start; }
            .ow-controls label { display: grid; gap: 4px; font-size: 12.5px; font-weight: 600; }
            .ow-check { display: flex !important; gap: 6px; align-items: center; }
            .ow-controls > .ow-row:last-child { grid-column: 1 / -1; }
            .ow-input { background: var(--input-bg, transparent); color: var(--text, inherit); border: 1px solid var(--line, rgba(127, 127, 127, .3)); border-radius: 8px; padding: 6px 8px; font: inherit; font-size: 13px; }
            .ow-input option { color: #111; }
            .ow-toast { position: fixed; left: 50%; bottom: 70px; transform: translateX(-50%); z-index: 9100; background: var(--panel-solid, #16202c); color: var(--text, #e8eef5); border: 1px solid var(--line, rgba(127, 127, 127, .3)); padding: 8px 16px; border-radius: 999px; box-shadow: 0 10px 30px rgba(0, 0, 0, .35); }
            @media (max-width: 700px) { .ow-hero, .ow-imagerbox { grid-template-columns: 1fr; } }
        </style>
        <script>
            (() => {
                const T = {
                    entered: @js(__("You're in! Good luck.")), shared: @js(__('Shared to your feed!')), copied: @js(__('Link copied.')),
                    oops: @js(__('Something went wrong. Try again.')), entry: @js(__("I entered Outfit of the Week! Vote for me 👕")),
                    closed: @js(__('Voting closed')),
                };
                const outfits = (cfg) => ({
                    entries: cfg.entries, winners: cfg.winners, busy: false, toast: '', countdown: '',
                    opts: { direction: 2, head_direction: 3, action: 'std', gesture: 'sml', size: 'l' }, headonly: false,
                    get mine() { return this.entries.find((e) => e.mine); },
                    start() { this.tick(); setInterval(() => this.tick(), 30000); },
                    tick() {
                        const left = new Date(cfg.ends) - new Date();
                        if (left <= 0) { this.countdown = T.closed; return; }
                        const d = Math.floor(left / 864e5), h = Math.floor(left / 36e5) % 24, m = Math.floor(left / 6e4) % 60;
                        this.countdown = (d ? `${d}d ` : '') + `${h}h ${m}m`;
                    },
                    async call(url, method = 'GET', body = null) {
                        const res = await fetch(url, {
                            method, credentials: 'same-origin',
                            headers: { Accept: 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '' },
                            body: body ? JSON.stringify(body) : null,
                        });
                        const data = await res.json().catch(() => ({}));
                        if (!res.ok) throw new Error(data.errors ? Object.values(data.errors)[0][0] : (data.message || T.oops));
                        return data;
                    },
                    figure(look) { return cfg.imager + encodeURIComponent(look) + '&direction=2&head_direction=3&gesture=sml&size=l'; },
                    query() {
                        const q = new URLSearchParams(Object.entries(this.opts).map(([k, v]) => [k, String(v)]));
                        if (this.headonly) q.set('headonly', '1');
                        return q.toString();
                    },
                    pose() { return cfg.imager + encodeURIComponent(cfg.me.look) + '&' + this.query(); },
                    async enter() {
                        this.busy = true;
                        try {
                            const data = await this.call(cfg.submit, 'POST', {});
                            this.entries = [...this.entries.filter((e) => e.id !== data.entry.id), { ...data.entry, votes: this.mine?.votes ?? 0 }];
                            this.flash(T.entered);
                        } catch (e) { this.flash(e.message); } finally { this.busy = false; }
                    },
                    async vote(entry) {
                        try { Object.assign(entry, await this.call(`${cfg.base}/${entry.id}/vote`, 'POST', {})); } catch (e) { this.flash(e.message); }
                    },
                    async shareEntry() {
                        try { await this.call(cfg.post, 'POST', { type: 'outfit', outfit_id: this.mine.id, body: T.entry }); this.flash(T.shared); } catch (e) { this.flash(e.message); }
                    },
                    async sharePose() {
                        try { await this.call(cfg.post, 'POST', { type: 'outfit', avatar: this.query() }); this.flash(T.shared); } catch (e) { this.flash(e.message); }
                    },
                    copyPose() { navigator.clipboard?.writeText(this.pose()).then(() => this.flash(T.copied)).catch(() => {}); },
                    flash(text) { this.toast = text; clearTimeout(this.timer); this.timer = setTimeout(() => (this.toast = ''), 2600); },
                });
                if (window.Alpine) window.Alpine.data('outfitWeek', outfits);
                else document.addEventListener('alpine:init', () => window.Alpine.data('outfitWeek', outfits));
            })();
        </script>
    @endpush
</x-app-layout>

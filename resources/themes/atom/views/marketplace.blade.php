<x-app-layout>
    @push('title', __('Marketplace'))

    <div class="col-span-12 mk" x-data="hotelMarket(@js($config))">
        <section class="mk-card mk-head">
            <div>
                <b class="mk-title">🛒 {{ __('Marketplace') }}</b>
                <p class="mk-muted">{{ __('See what is for sale in the hotel, what sold lately and who owns which rares. Buy and sell from the Marketplace page in the in-game catalog.') }}</p>
            </div>
            <div class="mk-tabs">
                <template x-for="t in tabs" :key="t.id">
                    <button type="button" :class="tab === t.id && 'on'" x-on:click="tab = t.id" x-text="t.label"></button>
                </template>
            </div>
        </section>

        <p class="mk-card mk-muted" x-show="!cfg.available" x-cloak>{{ __("The Marketplace isn't available on this hotel.") }}</p>

        <section class="mk-card" x-show="tab === 'sale'">
            <form class="mk-row" x-on:submit.prevent="find()">
                <input class="mk-input" x-model="q" placeholder="{{ __('Search furni for sale') }}">
                <button class="mk-btn mk-primary">{{ __('Search') }}</button>
            </form>
            <div class="mk-grid">
                <template x-for="o in listings" :key="o.id">
                    <article class="mk-item">
                        <img :src="o.icon" alt="" loading="lazy" x-on:error="$el.style.visibility = 'hidden'">
                        <div class="mk-grow">
                            <b x-text="o.name"></b>
                            <span class="mk-ltd" x-show="o.ltd" x-text="'LTD ' + o.ltd"></span>
                            <div class="mk-muted mk-small"><span x-text="o.seller"></span> · <span x-text="o.ago"></span></div>
                        </div>
                        <div class="mk-price">🪙 <span x-text="o.price.toLocaleString()"></span></div>
                        <button type="button" class="mk-iconbtn" title="{{ __('Share to feed') }}" x-on:click="share(o)">🔁</button>
                    </article>
                </template>
            </div>
            <p class="mk-muted mk-empty" x-show="!listings.length">{{ __('Nothing for sale right now.') }}</p>
        </section>

        <section class="mk-card" x-show="tab === 'sold'" x-cloak>
            <div class="mk-grid">
                <template x-for="o in sales" :key="o.id">
                    <article class="mk-item">
                        <img :src="o.icon" alt="" loading="lazy" x-on:error="$el.style.visibility = 'hidden'">
                        <div class="mk-grow"><b x-text="o.name"></b><div class="mk-muted mk-small">{{ __('Sold by') }} <span x-text="o.seller"></span></div></div>
                        <div class="mk-price">🪙 <span x-text="o.price.toLocaleString()"></span></div>
                    </article>
                </template>
            </div>
            <p class="mk-muted mk-empty" x-show="!sales.length">{{ __('No sales yet.') }}</p>
        </section>

        <section class="mk-card" x-show="tab === 'movers'" x-cloak>
            <ol class="mk-movers">
                <template x-for="m in cfg.movers" :key="m.name">
                    <li class="mk-item">
                        <img :src="m.icon" alt="" loading="lazy" x-on:error="$el.style.visibility = 'hidden'">
                        <div class="mk-grow"><b x-text="m.name"></b><div class="mk-muted mk-small"><span x-text="m.sold"></span> {{ __('sold') }}</div></div>
                        <div class="mk-price">{{ __('avg') }} 🪙 <span x-text="m.average.toLocaleString()"></span></div>
                    </li>
                </template>
            </ol>
            <p class="mk-muted mk-empty" x-show="!cfg.movers.length">{{ __('Nothing has sold yet.') }}</p>
        </section>

        <section class="mk-card" x-show="tab === 'rares'" x-cloak>
            <div class="mk-row mk-between">
                <input class="mk-input" x-model="rareQ" placeholder="{{ __('Find a rare') }}">
                <label class="mk-check"><input type="checkbox" :checked="hidden" x-on:change="hide($event.target.checked)"> {{ __('Hide me from this list') }}</label>
            </div>
            <div class="mk-rares">
                <template x-for="r in rares" :key="r.id">
                    <div class="mk-rare" :class="open === r.id && 'on'">
                        <button type="button" class="mk-item" x-on:click="owners(r)">
                            <img :src="r.icon" alt="" loading="lazy" x-on:error="$el.style.visibility = 'hidden'">
                            <b class="mk-grow" x-text="r.name"></b>
                            <span class="mk-muted mk-small" x-show="r.value" x-text="'🪙 ' + r.value"></span>
                            <span x-text="open === r.id ? '▾' : '▸'"></span>
                        </button>
                        <ul class="mk-owners" x-show="open === r.id">
                            <template x-for="o in (list[r.id] || [])" :key="o.name">
                                <li><a :href="o.home" x-text="o.name"></a> <span class="mk-muted" x-text="'× ' + o.count"></span></li>
                            </template>
                            <li class="mk-muted" x-show="list[r.id] && !list[r.id].length">{{ __('Nobody owns this yet.') }}</li>
                        </ul>
                    </div>
                </template>
            </div>
            <p class="mk-muted mk-empty" x-show="!cfg.rares.length">{{ __('No rare values have been set up yet.') }}</p>
        </section>

        <div class="mk-toast" x-show="toast" x-transition x-cloak x-text="toast"></div>
    </div>

    @push('javascript')
        <style>
            [x-cloak] { display: none !important; }
            .mk { display: grid; gap: 14px; }
            .mk-card { background: var(--panel, rgba(127, 127, 127, .08)); border: 1px solid var(--line, rgba(127, 127, 127, .2)); border-radius: var(--radius, 14px); padding: 14px; display: grid; gap: 10px; min-width: 0; margin: 0; }
            .mk-head { grid-template-columns: 1fr auto; align-items: center; }
            .mk-title { font-size: 18px; }
            .mk-muted { color: var(--muted, #8396ad); font-size: 13px; margin: 0; } .mk-small { font-size: 12px; }
            .mk-row { display: flex; gap: 8px; align-items: center; flex-wrap: wrap; } .mk-between { justify-content: space-between; }
            .mk-tabs { display: flex; gap: 4px; flex-wrap: wrap; }
            .mk-tabs button { background: none; border: 1px solid transparent; color: var(--muted, #8396ad); border-radius: 999px; padding: 5px 12px; font: inherit; font-size: 13px; cursor: pointer; }
            .mk-tabs button.on { border-color: var(--accent, #4da3ff); color: var(--white, inherit); background: var(--hover-row, rgba(77, 163, 255, .08)); }
            .mk-input { flex: 1; min-width: 160px; background: var(--input-bg, transparent); color: var(--text, inherit); border: 1px solid var(--line, rgba(127, 127, 127, .3)); border-radius: 8px; padding: 7px 10px; font: inherit; font-size: 13px; }
            .mk-btn { background: var(--panel-2, rgba(127, 127, 127, .12)); border: 1px solid var(--line, rgba(127, 127, 127, .25)); color: var(--text, inherit); border-radius: 9px; padding: 6px 12px; font: inherit; font-size: 13px; cursor: pointer; }
            .mk-primary { background: var(--accent, #4da3ff); border-color: var(--accent, #4da3ff); color: var(--on-accent, #06121f); font-weight: 600; }
            .mk-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 8px; }
            .mk-item { display: flex; gap: 10px; align-items: center; padding: 8px 10px; border-radius: 10px; background: var(--soft, rgba(127, 127, 127, .1)); min-width: 0; }
            button.mk-item { width: 100%; border: 0; color: inherit; font: inherit; text-align: left; cursor: pointer; }
            .mk-item img { width: 36px; height: 36px; object-fit: contain; image-rendering: pixelated; flex: none; }
            .mk-grow { flex: 1; min-width: 0; } .mk-grow b { overflow-wrap: anywhere; }
            .mk-ltd { margin-left: 6px; font-size: 11px; padding: 1px 6px; border-radius: 999px; background: #e5484d; color: #fff; }
            .mk-price { font-weight: 700; white-space: nowrap; font-size: 13px; }
            .mk-iconbtn { background: none; border: 0; cursor: pointer; font-size: 15px; padding: 4px; border-radius: 6px; }
            .mk-iconbtn:hover { background: var(--hover-row, rgba(77, 163, 255, .1)); }
            .mk-movers { list-style: none; margin: 0; padding: 0; display: grid; gap: 6px; counter-reset: mover; }
            .mk-movers li::before { counter-increment: mover; content: counter(mover); width: 22px; text-align: center; font-weight: 700; color: var(--muted, #8396ad); }
            .mk-rares { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 6px; align-items: start; }
            .mk-owners { list-style: none; margin: 4px 0 0; padding: 6px 12px; display: grid; gap: 3px; font-size: 13px; }
            .mk-owners a { color: var(--accent, #4da3ff); text-decoration: none; }
            .mk-check { display: inline-flex; gap: 6px; align-items: center; font-size: 13px; }
            .mk-empty { text-align: center; padding: 20px; }
            .mk-toast { position: fixed; left: 50%; bottom: 70px; transform: translateX(-50%); z-index: 9100; background: var(--panel-solid, #16202c); color: var(--text, #e8eef5); border: 1px solid var(--line, rgba(127, 127, 127, .3)); padding: 8px 16px; border-radius: 999px; box-shadow: 0 10px 30px rgba(0, 0, 0, .35); }
            @media (max-width: 700px) { .mk-head { grid-template-columns: 1fr; } }
        </style>
        <script>
            (() => {
                const T = { shared: @js(__('Shared to your feed!')), oops: @js(__('Something went wrong. Try again.')), saved: @js(__('Saved')) };
                const market = (cfg) => ({
                    cfg, tab: 'sale', q: '', rareQ: '', listings: cfg.listings, sales: cfg.sales, hidden: cfg.hidden, open: null, list: {}, toast: '',
                    tabs: [
                        { id: 'sale', label: @js('🏷️ ' . __('For sale')) }, { id: 'sold', label: @js('✅ ' . __('Recent sales')) },
                        { id: 'movers', label: @js('📈 ' . __('Top movers')) }, { id: 'rares', label: @js('💎 ' . __('Rare directory')) },
                    ],
                    get rares() { const q = this.rareQ.trim().toLowerCase(); return q ? cfg.rares.filter((r) => r.name.toLowerCase().includes(q)) : cfg.rares; },
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
                    async find() { try { this.listings = (await this.call(cfg.search + '?q=' + encodeURIComponent(this.q))).listings; } catch (e) { this.flash(e.message); } },
                    async share(offer) { try { await this.call(cfg.post, 'POST', { type: 'listing', offer_id: offer.id }); this.flash(T.shared); } catch (e) { this.flash(e.message); } },
                    async owners(rare) {
                        this.open = this.open === rare.id ? null : rare.id;
                        if (this.open && !this.list[rare.id]) {
                            try { this.list = { ...this.list, [rare.id]: (await this.call(`${cfg.owners}/${rare.id}`)).owners }; } catch { this.list = { ...this.list, [rare.id]: [] }; }
                        }
                    },
                    async hide(on) { try { await this.call(cfg.profile, 'POST', { hide_from_rare_directory: on }); this.hidden = on; this.flash(T.saved); } catch (e) { this.flash(e.message); } },
                    flash(text) { this.toast = text; clearTimeout(this.timer); this.timer = setTimeout(() => (this.toast = ''), 2400); },
                });
                if (window.Alpine) window.Alpine.data('hotelMarket', market);
                else document.addEventListener('alpine:init', () => window.Alpine.data('hotelMarket', market));
            })();
        </script>
    @endpush
</x-app-layout>

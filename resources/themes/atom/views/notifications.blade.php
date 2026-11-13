<x-app-layout>
    @push('title', __('Notifications'))

    <div class="col-span-12 nt" x-data="hotelNotifications(@js([
        'feed' => route('notifications.feed', absolute: false),
        'read' => route('notifications.read', absolute: false),
        'clear' => route('notifications.clear', absolute: false),
        'profile' => route('social.profile', absolute: false),
        'muted' => array_values($muted),
    ]))" x-init="start()">
        <section class="nt-card">
            <div class="nt-row nt-between">
                <b class="nt-title">🔔 {{ __('Notifications') }} <span class="nt-count" x-show="unread" x-text="unread"></span></b>
                <span class="nt-row">
                    <button type="button" class="nt-btn" x-on:click="markAll()" :disabled="!unread">{{ __('Mark all read') }}</button>
                    <button type="button" class="nt-btn" x-on:click="clearAll()" :disabled="!items.length">{{ __('Clear') }}</button>
                </span>
            </div>
            <template x-for="n in items" :key="n.id">
                <a class="nt-item" :class="!n.read && 'new'" :href="n.url || '#'" x-on:click="open(n, $event)">
                    <template x-if="n.actor"><img :src="n.actor.head" alt=""></template>
                    <template x-if="!n.actor"><span class="nt-icon">🏨</span></template>
                    <span class="nt-grow"><span x-text="n.message"></span><small x-text="n.ago"></small></span>
                    <span class="nt-dot" x-show="!n.read"></span>
                </a>
            </template>
            <p class="nt-muted nt-empty" x-show="loaded && !items.length">{{ __("You're all caught up.") }}</p>
        </section>

        <section class="nt-card">
            <b class="nt-title">⚙️ {{ __('What I get notified about') }}</b>
            <p class="nt-muted">{{ __('Untick anything you would rather not hear about.') }}</p>
            @foreach ($types as $group => $kinds)
                <fieldset class="nt-group">
                    <legend>{{ __($group) }}</legend>
                    @foreach ($kinds as $key => $label)
                        <label class="nt-check"><input type="checkbox" :checked="!muted.includes(@js($key))" x-on:change="toggle(@js($key), $event.target.checked)"> {{ __($label) }}</label>
                    @endforeach
                </fieldset>
            @endforeach
            <span class="nt-saved" x-show="saved" x-transition>{{ __('Saved') }}</span>
        </section>
    </div>

    @push('javascript')
        <style>
            .nt { display: grid; grid-template-columns: minmax(0, 1fr) 320px; gap: 16px; align-items: start; }
            .nt-card { background: var(--panel, rgba(127, 127, 127, .08)); border: 1px solid var(--line, rgba(127, 127, 127, .2)); border-radius: var(--radius, 14px); padding: 14px; display: grid; gap: 6px; min-width: 0; }
            .nt-row { display: flex; gap: 8px; align-items: center; flex-wrap: wrap; } .nt-between { justify-content: space-between; }
            .nt-title { font-size: 16px; } .nt-muted { color: var(--muted, #8396ad); font-size: 13px; }
            .nt-count { background: var(--bad, #ff6b6b); color: #fff; border-radius: 999px; padding: 0 7px; font-size: 12px; }
            .nt-btn { background: var(--panel-2, rgba(127, 127, 127, .12)); border: 1px solid var(--line, rgba(127, 127, 127, .25)); color: var(--text, inherit); border-radius: 9px; padding: 5px 11px; font: inherit; font-size: 13px; cursor: pointer; }
            .nt-btn:disabled { opacity: .5; cursor: default; }
            .nt-item { display: flex; gap: 10px; align-items: center; padding: 8px; border-radius: 10px; color: inherit; text-decoration: none; }
            .nt-item:hover { background: var(--hover-row, rgba(77, 163, 255, .1)); }
            .nt-item.new { background: var(--hover-row, rgba(77, 163, 255, .07)); }
            .nt-item img { width: 36px; height: 36px; object-fit: none; border-radius: 8px; background: var(--soft, rgba(127, 127, 127, .15)); flex: none; }
            .nt-icon { width: 36px; height: 36px; display: grid; place-items: center; font-size: 20px; flex: none; }
            .nt-grow { flex: 1; display: grid; font-size: 13.5px; } .nt-grow small { color: var(--muted, #8396ad); font-size: 11.5px; }
            .nt-dot { width: 9px; height: 9px; border-radius: 50%; background: var(--accent, #4da3ff); flex: none; }
            .nt-empty { text-align: center; padding: 24px; }
            .nt-group { border: 0; padding: 6px 0 0; margin: 0; display: grid; gap: 4px; }
            .nt-group legend { font-weight: 700; font-size: 13px; padding: 0; margin-bottom: 2px; }
            .nt-check { display: flex; gap: 8px; align-items: center; font-size: 13px; cursor: pointer; }
            .nt-saved { color: var(--good, #3ddc97); font-size: 13px; }
            @media (max-width: 860px) { .nt { grid-template-columns: 1fr; } }
        </style>
        <script>
            (() => {
                const notifications = (urls) => ({
                    items: [], unread: 0, loaded: false, muted: urls.muted, saved: false,
                    start() { this.load(); setInterval(() => { if (!document.hidden) this.load(); }, 20000); },
                    async call(url, method = 'GET', body = null) {
                        const res = await fetch(url, {
                            method, credentials: 'same-origin',
                            headers: { Accept: 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '' },
                            body: body ? JSON.stringify(body) : null,
                        });
                        if (!res.ok) throw new Error(String(res.status));
                        return res.json();
                    },
                    async load() {
                        try { const data = await this.call(urls.feed); this.items = data.notifications; this.unread = data.unread; } catch { /* try again later */ }
                        this.loaded = true;
                    },
                    async open(n, event) {
                        if (!n.url) event.preventDefault();
                        if (!n.read) { n.read = true; this.unread = Math.max(0, this.unread - 1); this.call(urls.read, 'POST', { id: n.id }).catch(() => {}); }
                    },
                    async markAll() { await this.call(urls.read, 'POST', {}); this.items.forEach((n) => (n.read = true)); this.unread = 0; },
                    async clearAll() { await this.call(urls.clear, 'DELETE'); this.items = []; this.unread = 0; },
                    async toggle(key, on) {
                        this.muted = on ? this.muted.filter((k) => k !== key) : [...new Set([...this.muted, key])];
                        try { await this.call(urls.profile, 'POST', { muted: this.muted }); this.saved = true; setTimeout(() => (this.saved = false), 1500); } catch { /* the box stays as clicked */ }
                    },
                });
                if (window.Alpine) window.Alpine.data('hotelNotifications', notifications);
                else document.addEventListener('alpine:init', () => window.Alpine.data('hotelNotifications', notifications));
            })();
        </script>
    @endpush
</x-app-layout>

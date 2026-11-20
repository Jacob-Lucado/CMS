<div id="hrm" class="hrm" data-feed="{{ route('radio.feed', absolute: false) }}" aria-live="polite">
    <section class="hrm-win" id="hrm-win" aria-label="{{ __('Hotel Radio') }}">
        <header class="hrm-bar" id="hrm-bar">
            <svg viewBox="0 0 16 16" width="18" height="18" shape-rendering="crispEdges" aria-hidden="true"><rect x="10" y="1" width="1" height="4" fill="#cfd8dc"/><rect x="9" y="4" width="2" height="1" fill="#cfd8dc"/><rect x="1" y="5" width="14" height="10" fill="#e8962a"/><rect x="1" y="5" width="14" height="1" fill="#f7c35f"/><rect x="1" y="14" width="14" height="1" fill="#a85f12"/><rect x="3" y="7" width="5" height="6" fill="#5a3a12"/><rect x="10" y="7" width="3" height="2" fill="#8dff8a"/><rect x="10" y="10" width="1" height="2" fill="#f2efe6"/><rect x="12" y="10" width="1" height="2" fill="#f2efe6"/></svg>
            <b>{{ __('Hotel Radio') }}</b>
            <span class="hrm-lamp"><i></i>{{ __('ON AIR') }}</span>
            <button type="button" class="hrm-x" id="hrm-hide" title="{{ __('Hide') }}" aria-label="{{ __('Hide the radio') }}">&ndash;</button>
        </header>
        <div class="hrm-body">
            <div class="hrm-lcd" id="hrm-lcd">
                <div class="hrm-text">
                    <b id="hrm-title"></b>
                    <small id="hrm-sub"></small>
                </div>
                <div class="hrm-eq" aria-hidden="true">@for ($i = 0; $i < 8; $i++)<i style="--d: {{ ($i * 5) % 11 }}"></i>@endfor</div>
            </div>
            <div class="hrm-ctl">
                <button type="button" class="hrm-btn" id="hrm-play" aria-label="{{ __('Play') }}">&#9654;</button>
                <label class="hrm-vol"><span>{{ __('VOL') }}</span><input type="range" id="hrm-vol" min="0" max="1" step="0.05" aria-label="{{ __('Volume') }}"></label>
            </div>
        </div>
    </section>
    <button type="button" class="hrm-tab" id="hrm-tab" title="{{ __('Show the radio') }}" aria-label="{{ __('Show the radio') }}">
        <svg viewBox="0 0 16 16" width="22" height="22" shape-rendering="crispEdges" aria-hidden="true"><rect x="10" y="1" width="1" height="4" fill="#cfd8dc"/><rect x="9" y="4" width="2" height="1" fill="#cfd8dc"/><rect x="1" y="5" width="14" height="10" fill="#e8962a"/><rect x="1" y="5" width="14" height="1" fill="#f7c35f"/><rect x="1" y="14" width="14" height="1" fill="#a85f12"/><rect x="3" y="7" width="5" height="6" fill="#5a3a12"/><rect x="10" y="7" width="3" height="2" fill="#8dff8a"/><rect x="10" y="10" width="1" height="2" fill="#f2efe6"/><rect x="12" y="10" width="1" height="2" fill="#f2efe6"/></svg>
        <i class="hrm-dot"></i>
    </button>
    <div class="hrm-stage" id="hrm-stage" aria-hidden="true"></div>
</div>

<style>
    .hrm { --edge: #14202a; --frame: #31495b; --blue: #2f86b8; --blue-d: #1d5f86; --green: #6cc22f; --green-d: #3f8a14; --red: #e0392b; --paper: #e9e5d8; --lcd: #0b1d14; --lcd-on: #8dff8a; --lcd-dim: #4f9d55;
        position: fixed; right: 14px; bottom: 72px; z-index: 15; width: 262px; font-family: Ubuntu, "Trebuchet MS", "Segoe UI", sans-serif; opacity: 0; visibility: hidden; transform: translateY(8px); transition: opacity .35s ease, transform .35s ease, visibility 0s linear .35s; }
    .hrm * { box-sizing: border-box; }
    .hrm.on { opacity: 1; visibility: visible; transform: none; transition-delay: 0s; }
    .hrm-win { background: var(--frame); border: 2px solid var(--edge); border-radius: 6px; box-shadow: inset 0 0 0 2px #4d6a80, 3px 3px 0 rgba(0, 0, 0, .35); overflow: hidden; transition: opacity .3s ease, transform .3s ease, visibility 0s; }
    .hrm.min .hrm-win { opacity: 0; visibility: hidden; transform: translateY(6px) scale(.97); pointer-events: none; transition: opacity .3s ease, transform .3s ease, visibility 0s linear .3s; }
    .hrm-bar { display: flex; align-items: center; gap: 6px; padding: 4px 6px 4px 8px; background: var(--blue); border-bottom: 2px solid var(--edge); color: #fff; box-shadow: inset 0 2px 0 rgba(255, 255, 255, .28); cursor: grab; touch-action: none; user-select: none; }
    .hrm-bar b { flex: 1; font-size: 12px; text-transform: uppercase; letter-spacing: .04em; text-shadow: 0 1px 0 var(--edge); }
    .hrm-lamp { display: inline-flex; align-items: center; gap: 5px; font-size: 10px; font-weight: 700; letter-spacing: .08em; padding: 1px 6px; background: var(--edge); border-radius: 3px; }
    .hrm-lamp i, .hrm-dot { width: 8px; height: 8px; background: var(--red); box-shadow: 0 0 5px var(--red); display: block; animation: hrm-blink 1.2s steps(1) infinite; }
    @keyframes hrm-blink { 50% { opacity: .35; } }
    .hrm-x { width: 20px; height: 18px; padding: 0; background: #f6f3ea; color: var(--edge); border: 2px solid var(--edge); border-radius: 3px; font-family: inherit; font-weight: 700; font-size: 14px; line-height: 1; cursor: pointer; box-shadow: inset 0 -2px 0 #b9b4a3, inset 0 1px 0 #fff; }
    .hrm-x:hover { background: #fff; }
    .hrm-body { margin: 6px; padding: 8px; background: var(--paper); border: 2px solid var(--edge); border-radius: 4px; display: grid; gap: 8px; }
    .hrm-lcd { display: flex; align-items: center; gap: 8px; min-height: 52px; padding: 7px 9px; background: var(--lcd); border: 2px solid var(--edge); border-radius: 3px; box-shadow: inset 0 3px 0 rgba(0, 0, 0, .55), inset 0 0 0 2px #17402b; color: var(--lcd-on); font-family: "Courier New", ui-monospace, monospace; text-shadow: 0 0 4px rgba(141, 255, 138, .4); }
    .hrm-text { flex: 1; min-width: 0; display: grid; gap: 2px; }
    .hrm-text b { font-size: 13px; text-transform: uppercase; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .hrm-text small { font-size: 11px; color: var(--lcd-dim); overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .hrm-eq { display: flex; align-items: flex-end; gap: 2px; width: 40px; height: 34px; flex: none; }
    .hrm-eq i { flex: 1; height: 15%; background: repeating-linear-gradient(to top, var(--lcd-on) 0 4px, transparent 4px 6px); opacity: .55; }
    .hrm-lcd.live .hrm-eq i { opacity: 1; animation: hrm-eq 1s steps(1, end) infinite; animation-delay: calc(var(--d) * -.09s); }
    @keyframes hrm-eq { 0% { height: 18%; } 15% { height: 78%; } 30% { height: 42%; } 45% { height: 100%; } 60% { height: 58%; } 80% { height: 30%; } 100% { height: 18%; } }
    .hrm-ctl { display: flex; align-items: center; gap: 10px; }
    .hrm-btn { width: 40px; height: 30px; padding: 0; background: var(--green); color: #fff; border: 2px solid var(--edge); border-radius: 4px; font-family: inherit; font-weight: 700; font-size: 14px; line-height: 1; text-shadow: 0 1px 0 var(--green-d); cursor: pointer; box-shadow: inset 0 -3px 0 var(--green-d), inset 0 2px 0 rgba(255, 255, 255, .4); }
    .hrm-btn:hover { background: #7bd13c; }
    .hrm-btn:active { transform: translateY(1px); box-shadow: inset 0 2px 0 var(--green-d); }
    .hrm-vol { display: flex; align-items: center; gap: 6px; flex: 1; font-size: 10.5px; font-weight: 700; letter-spacing: .1em; color: #1d2b36; }
    .hrm-vol input { flex: 1; min-width: 0; accent-color: var(--blue); height: 8px; cursor: pointer; }
    .hrm-tab { position: absolute; right: 0; bottom: 0; width: 40px; height: 40px; padding: 0; display: grid; place-items: center; background: var(--frame); border: 2px solid var(--edge); border-radius: 6px; box-shadow: inset 0 0 0 2px #4d6a80, 3px 3px 0 rgba(0, 0, 0, .35); cursor: pointer; opacity: 0; visibility: hidden; transform: scale(.9); transition: opacity .3s ease, transform .3s ease, visibility 0s linear .3s; }
    .hrm.min .hrm-tab { opacity: 1; visibility: visible; transform: none; transition-delay: 0s; }
    .hrm-tab:hover { background: #3b556a; }
    .hrm-dot { position: absolute; top: 3px; right: 3px; width: 7px; height: 7px; }
    .hrm-stage { position: absolute; width: 1px; height: 1px; overflow: hidden; opacity: 0; pointer-events: none; }
    .hrm button:focus-visible, .hrm input:focus-visible { outline: 2px solid #fff; outline-offset: 2px; }
    @media (prefers-reduced-motion: reduce) { .hrm, .hrm-win, .hrm-tab { transition-duration: 0s; } .hrm-lamp i, .hrm-dot, .hrm-lcd.live .hrm-eq i { animation: none; } }
</style>

<script>
    (() => {
        const root = document.getElementById('hrm');
        if (!root) return;
        const WIDGET = 'https://w.soundcloud.com';
        const KEY = 'hotel-radio';
        const POS = 'hotel-radio-mini';
        const read = (key) => { try { return JSON.parse(localStorage.getItem(key) || '{}'); } catch { return {}; } };
        const write = (key, value) => { try { localStorage.setItem(key, JSON.stringify(value)); } catch { /* fine without it */ } };
        const $ = (id) => document.getElementById(id);
        const [win, bar, title, sub, lcd, play, vol, stage] = ['hrm-win', 'hrm-bar', 'hrm-title', 'hrm-sub', 'hrm-lcd', 'hrm-play', 'hrm-vol', 'hrm-stage'].map($);
        const labels = { play: @js(__('Play')), pause: @js(__('Pause')), tune: @js(__('Click play to tune in')) };

        let onAir = null, audio = null, frame = null, playing = false, listening = false;
        let volume = read(KEY).volume ?? 0.8;
        vol.value = volume;

        const saved = read(POS);
        root.classList.toggle('min', !!saved.hidden);
        if (Number.isFinite(saved.x) && Number.isFinite(saved.y)) place(saved.x, saved.y);

        function place(x, y) {
            const r = root.getBoundingClientRect();
            const left = Math.min(Math.max(0, x), innerWidth - r.width);
            const top = Math.min(Math.max(0, y), innerHeight - r.height);
            Object.assign(root.style, { left: left + 'px', top: top + 'px', right: 'auto', bottom: 'auto' });
            return { x: left, y: top };
        }

        function paint() {
            play.textContent = playing ? '❚❚' : '▶';
            play.setAttribute('aria-label', playing ? labels.pause : labels.play);
            lcd.classList.toggle('live', playing);
            if (onAir) {
                title.textContent = onAir.title;
                sub.textContent = listening ? (onAir.subtitle || '') : labels.tune;
            }
        }

        function send(message) {
            try { frame.contentWindow.postMessage(JSON.stringify(message), WIDGET); } catch { /* the ready message covers it */ }
        }

        function clear() {
            if (audio) audio.pause();
            audio = null; frame = null; playing = false;
            stage.replaceChildren();
        }

        function start(entry) {
            clear();
            listening = true;
            if (entry.kind === 'station') {
                audio = Object.assign(document.createElement('audio'), { autoplay: true, src: entry.stream, volume });
                audio.addEventListener('play', () => { playing = true; paint(); });
                audio.addEventListener('pause', () => { playing = false; paint(); });
                stage.append(audio);
                audio.play().catch(() => {});
            } else {
                frame = document.createElement('iframe');
                frame.allow = 'autoplay; encrypted-media';
                frame.title = entry.title;
                frame.height = '166';
                frame.src = `${WIDGET}/player/?url=${encodeURIComponent(entry.stream)}&auto_play=true&hide_related=true&show_comments=false&visual=false`;
                frame.addEventListener('load', subscribe);
                stage.append(frame);
                playing = true;
            }
            paint();
        }

        function subscribe() {
            if (!frame) return;
            ['play', 'pause', 'finish'].forEach((value) => send({ method: 'addEventListener', value }));
            send({ method: 'setVolume', value: Math.round(volume * 100) });
        }

        window.addEventListener('message', (event) => {
            if (!frame || event.source !== frame.contentWindow || event.origin !== WIDGET) return;
            let data = event.data;
            try { if (typeof data === 'string') data = JSON.parse(data); } catch { return; }
            if (data?.method === 'ready') subscribe();
            if (data?.method === 'play') playing = true;
            if (data?.method === 'pause' || data?.method === 'finish') playing = false;
            paint();
        });

        play.addEventListener('click', () => {
            if (!onAir) return;
            if (!listening) return start(onAir);
            if (audio) {
                if (audio.paused) { audio.src = onAir.stream; audio.play().catch(() => {}); } else audio.pause();
            } else if (frame) send({ method: 'toggle' });
        });

        vol.addEventListener('input', () => {
            volume = Number(vol.value);
            write(KEY, { ...read(KEY), volume });
            if (audio) audio.volume = volume;
            else if (frame) send({ method: 'setVolume', value: Math.round(volume * 100) });
        });

        const setHidden = (hidden) => {
            root.classList.toggle('min', hidden);
            write(POS, { ...read(POS), hidden });
        };
        $('hrm-hide').addEventListener('click', () => setHidden(true));
        $('hrm-tab').addEventListener('click', () => setHidden(false));

        // Drag the window by its title bar; the frame underneath never sees the pointer.
        let drag = null;
        bar.addEventListener('pointerdown', (event) => {
            if (event.target.closest('button')) return;
            const r = root.getBoundingClientRect();
            drag = { dx: event.clientX - r.left, dy: event.clientY - r.top };
            bar.setPointerCapture(event.pointerId);
        });
        bar.addEventListener('pointermove', (event) => { if (drag) place(event.clientX - drag.dx, event.clientY - drag.dy); });
        bar.addEventListener('pointerup', () => {
            if (!drag) return;
            drag = null;
            const r = root.getBoundingClientRect();
            write(POS, { ...read(POS), x: r.left, y: r.top });
        });
        addEventListener('resize', () => { if (root.style.left) place(parseFloat(root.style.left), parseFloat(root.style.top)); });

        async function poll() {
            if (document.hidden) return;
            let data;
            try {
                const res = await fetch(root.dataset.feed, { credentials: 'same-origin', headers: { Accept: 'application/json' } });
                if (!res.ok) return;
                data = await res.json();
            } catch { return; }
            const next = data.on_air || null;
            const changed = (next?.id ?? null) !== (onAir?.id ?? null);
            onAir = next;
            if (!next) {
                clear();
                listening = false;
                root.classList.remove('on');
                return;
            }
            root.classList.add('on');
            if (changed && listening) start(next);
            else paint();
        }

        poll();
        setInterval(poll, 15000);
    })();
</script>

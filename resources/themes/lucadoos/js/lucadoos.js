// LucadoOS for the hotel: every part of the site is an icon on a desktop, and every page opens in a window you can drag,
// resize, snap, minimize and stack, with a taskbar and a Start menu along the bottom. Each window is a real page (the first
// one is rendered right into the desktop, the rest load in frames), so forms, Livewire and the client all work as usual.
// LucadoOS™ is a trademark of Jacob Lucado.

const store = {
    get(key) {
        try {
            return localStorage.getItem(key);
        } catch {
            return null;
        }
    },
    set(key, value) {
        try {
            localStorage.setItem(key, value);
        } catch {
            // private window: nothing is remembered, everything still works
        }
    },
};

// Signed in and signed out each keep their own windows, so the welcome page never comes back after signing in.
const SAVE = document.documentElement.dataset.losAuth === "1" ? "lucadoos-windows" : "lucadoos-windows-guest";
const PREFS = "lucadoos-prefs";
const PINS = "lucadoos-pins";
const NARROW = 900;

/** An app icon: a picture when it is a path or link, otherwise the emoji itself. */
function paintIcon(node, icon) {
    node.replaceChildren();
    if (/^(\/|https?:)/.test(icon || "")) {
        node.append(el("img", "los-pixel", { src: icon, alt: "", draggable: "false" }));
    } else {
        node.textContent = icon || "";
    }
    return node;
}

function el(tag, cls, attrs = {}, ...kids) {
    const node = document.createElement(tag);
    if (cls) node.className = cls;
    for (const [key, value] of Object.entries(attrs)) {
        if (value === null || value === undefined) continue;
        if (key === "text") node.textContent = value;
        else node.setAttribute(key, value);
    }
    node.append(...kids.filter(Boolean));
    return node;
}

const clamp = (value, low, high) => Math.max(low, Math.min(high, value));
const sameOrigin = (url) => url.origin === location.origin;

/** Drops our own "los" query flag, so addresses look like the normal site. */
function cleanPath(url) {
    const u = new URL(url, location.origin);
    u.searchParams.delete("los");
    return u.pathname + (u.search === "?" ? "" : u.search) + u.hash;
}

function embedUrl(path) {
    const u = new URL(path, location.origin);
    u.searchParams.set("los", "embed");
    return u.pathname + u.search + u.hash;
}

/* ---------------------------------------------------------------- theme */

const Theme = {
    current: () => (document.documentElement.dataset.theme === "light" ? "light" : "dark"),
    apply(theme) {
        const root = document.documentElement;
        root.dataset.theme = theme;
        root.classList.toggle("dark", theme === "dark");
        document.querySelector('meta[name="theme-color"]')?.setAttribute("content", theme === "light" ? "#e4eaf3" : "#0f1724");
        window.dispatchEvent(new CustomEvent("los-theme", { detail: theme }));
    },
    toggle() {
        const next = this.current() === "light" ? "dark" : "light";
        store.set("theme", next);
        this.apply(next);
        return next;
    },
};

/* ---------------------------------------------------------- neon waves */

/** A few glowing lines rolling slowly over the wallpaper. Stands still for reduced motion and pauses in a background tab. */
const Waves = (() => {
    const LINES = [
        { y: 0.52, amp: 46, len: 1.4, speed: 0.00032, phase: 0, hue: 212, width: 2.2 },
        { y: 0.6, amp: 38, len: 1.9, speed: -0.00026, phase: 1.7, hue: 256, width: 2 },
        { y: 0.69, amp: 54, len: 1.1, speed: 0.00021, phase: 3.1, hue: 188, width: 2.4 },
        { y: 0.78, amp: 30, len: 2.3, speed: -0.00036, phase: 4.6, hue: 232, width: 1.6 },
        { y: 0.87, amp: 42, len: 1.6, speed: 0.00028, phase: 5.8, hue: 276, width: 1.8 },
        { y: 0.44, amp: 28, len: 2.6, speed: -0.0002, phase: 2.4, hue: 172, width: 1.4 },
    ];
    let canvas = null;
    let ctx = null;
    let host = null;
    let watcher = null;
    let raf = 0;
    let last = 0;
    let still = false;
    let width = 0;
    let height = 0;

    function size() {
        const dpr = Math.min(2, window.devicePixelRatio || 1);
        const fixed = host === document.body;
        width = fixed ? window.innerWidth : host.clientWidth || window.innerWidth;
        height = fixed ? window.innerHeight : host.clientHeight || window.innerHeight;
        canvas.width = Math.round(width * dpr);
        canvas.height = Math.round(height * dpr);
        ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
    }

    function frame(now) {
        const dark = Theme.current() === "dark";
        const step = width < 600 ? 10 : 6;
        const dt = Math.min(64, now - (last || now));
        last = now;
        ctx.clearRect(0, 0, width, height);
        ctx.globalCompositeOperation = dark ? "lighter" : "source-over";

        for (const line of LINES) {
            const base = line.y * height;
            const k = (Math.PI * 2 * line.len) / width;
            line.t = (line.t || 0) + (still ? 0 : dt * line.speed * 6);
            const p = line.phase + line.t;

            ctx.beginPath();
            for (let x = -10; x <= width + 10; x += step) {
                const y = base + Math.sin(x * k + p) * line.amp + Math.sin(x * k * 2.3 - p * 1.4) * (line.amp * 0.35) + Math.sin(x * k * 0.5 + p * 0.6) * (line.amp * 0.5);
                if (x === -10) ctx.moveTo(x, y);
                else ctx.lineTo(x, y);
            }

            const alpha = dark ? 0.68 : 0.42;
            const light = dark ? 62 : 48;
            const gradient = ctx.createLinearGradient(0, 0, width, 0);
            gradient.addColorStop(0, `hsla(${line.hue}, 100%, ${light}%, 0)`);
            gradient.addColorStop(0.25, `hsla(${line.hue}, 100%, ${light}%, ${alpha})`);
            gradient.addColorStop(0.75, `hsla(${(line.hue + 40) % 360}, 100%, ${light}%, ${alpha})`);
            gradient.addColorStop(1, `hsla(${(line.hue + 40) % 360}, 100%, ${light}%, 0)`);
            ctx.strokeStyle = gradient;
            ctx.lineWidth = line.width;
            ctx.lineJoin = "round";

            if (dark) {
                // a wide faint pass under the line reads as a glow, far cheaper than a real blur
                ctx.globalAlpha = 0.18;
                ctx.lineWidth = line.width * 6;
                ctx.stroke();
                ctx.globalAlpha = 1;
                ctx.lineWidth = line.width;
            }

            ctx.stroke();
        }

        if (!still && canvas) raf = requestAnimationFrame(frame);
    }

    function onVisibility() {
        if (!canvas || still) return;
        if (document.hidden) cancelAnimationFrame(raf);
        else raf = requestAnimationFrame(frame);
    }

    return {
        start(target) {
            if (canvas && host === target) return;
            this.stop();
            host = target;
            still = Boolean(window.matchMedia?.("(prefers-reduced-motion: reduce)").matches);
            canvas = el("canvas", "los-waves", { "aria-hidden": "true" });
            ctx = canvas.getContext("2d");
            if (!ctx) {
                canvas = null;
                return;
            }
            host.prepend(canvas);
            size();
            window.addEventListener("resize", size);
            if (window.ResizeObserver && host !== document.body) {
                watcher = new ResizeObserver(size);
                watcher.observe(host);
            }
            document.addEventListener("visibilitychange", onVisibility);
            raf = requestAnimationFrame(frame);
        },
        stop() {
            if (!canvas) return;
            cancelAnimationFrame(raf);
            window.removeEventListener("resize", size);
            document.removeEventListener("visibilitychange", onVisibility);
            watcher?.disconnect();
            canvas.remove();
            canvas = ctx = watcher = host = null;
        },
    };
})();

/* ------------------------------------------------------------ desktop */

function desktop(data) {
    const S = data.strings;
    const desk = document.getElementById("los-desk");
    const layer = desk.querySelector(".los-layer");
    const iconsBox = desk.querySelector(".los-icons");
    const wins = new Map();
    let wavesReady = false;
    let z = 10;
    let cascade = 0;
    let seq = 0;
    let closeStart = () => {};

    // every page we know about, so a window can show the right name and icon for wherever it is
    const pages = {};
    for (const group of data.groups) for (const page of group.pages) pages[page.path] = page;
    pages["os:personalize"] = { path: "os:personalize", title: S.personalize, icon: "🎨" };
    pages["os:signin"] = { path: "os:signin", title: S.signIn, icon: "🔑" };

    const isClient = (path) => data.clients.some((client) => path.split("?")[0] === client);
    const metaFor = (path) => {
        const bare = path.split("?")[0].split("#")[0];
        if (pages[bare]) return pages[bare];
        // the longest known page that this address sits under, e.g. an article under the article list
        const parent = Object.keys(pages)
            .filter((key) => key !== "/" && key.startsWith("/") && bare.startsWith(key + "/"))
            .sort((a, b) => b.length - a.length)[0];
        return parent ? { ...pages[parent], title: null } : { title: null, icon: "📄" };
    };

    const narrow = () => window.innerWidth < NARROW;
    document.body.classList.toggle("los-narrow", narrow());

    /* ---------------------------------------------------- snapping */

    const ZONES = { L: [0, 0, 0.5, 1], R: [0.5, 0, 0.5, 1], TL: [0, 0, 0.5, 0.5], TR: [0.5, 0, 0.5, 0.5], BL: [0, 0.5, 0.5, 0.5], BR: [0.5, 0.5, 0.5, 0.5], L23: [0, 0, 2 / 3, 1], R13: [2 / 3, 0, 1 / 3, 1], C1: [0, 0, 1 / 3, 1], C2: [1 / 3, 0, 1 / 3, 1], C3: [2 / 3, 0, 1 / 3, 1] };
    const LAYOUTS = [["L", "R"], ["L23", "R13"], ["C1", "C2", "C3"], ["TL", "TR", "BL", "BR"]];
    const zoneRect = (zone) => {
        const [a, b, c, d] = ZONES[zone];
        const W = layer.clientWidth;
        const H = layer.clientHeight;
        return { x: Math.round(a * W), y: Math.round(b * H), w: Math.round(c * W), h: Math.round(d * H) };
    };

    let preview = null;
    function showPreview(zone) {
        if (!zone) {
            preview?.remove();
            preview = null;
            return;
        }
        if (!preview) {
            preview = el("div", "los-snap-preview", { "aria-hidden": "true" });
            layer.append(preview);
        }
        const r = zone === "MAX" ? { x: 0, y: 0, w: layer.clientWidth, h: layer.clientHeight } : zoneRect(zone);
        Object.assign(preview.style, { left: r.x + "px", top: r.y + "px", width: r.w + "px", height: r.h + "px" });
    }

    /* ------------------------------------------------- persistence */

    function persist() {
        const list = [...wins.values()]
            .filter((w) => !w.native)
            .map((w) => ({ path: w.path, x: w.x, y: w.y, w: w.w, h: w.h, max: w.max, min: w.min, snap: w.snap || null, z: Number(w.node.style.zIndex) || 0 }));
        store.set(SAVE, JSON.stringify(list));
        syncWaves();
    }

    /**
     * The waves roll in the top full-screen window when there is one, between its background and the page,
     * and on the desktop otherwise. Only one place animates at a time.
     */
    let wavesAt = null;
    function syncWaves() {
        if (!wavesReady) return;
        const zOf = (w) => Number(w.node.style.zIndex) || 0;
        const cover = [...wins.values()]
            .filter((w) => !w.min && !w.node.hidden && w.kind !== "native" && (w.max || narrow()))
            .sort((a, b) => zOf(b) - zOf(a))[0] || null;
        const target = !prefs.waves ? null : cover || desk;
        if (target === wavesAt) return;

        if (wavesAt && wavesAt !== desk && wavesAt.frame) tellFrame(wavesAt, false);
        if (wavesAt && wavesAt !== desk) wavesAt.body.classList.remove("los-waves-host");
        wavesAt = target;

        if (!target) return Waves.stop();
        if (target === desk) return Waves.start(desk);
        if (target.kind === "frame") {
            Waves.stop();
            tellFrame(target, true);
            return;
        }
        target.body.classList.add("los-waves-host");
        Waves.start(target.body);
    }

    function tellFrame(w, on) {
        try {
            w.frame.contentWindow?.postMessage({ los: "waves", on }, location.origin);
        } catch {
            // not loaded yet; its load handler asks again
        }
    }

    /** The browser's address follows the window you're in, so a copied link or a refresh opens the same page. */
    function addressOf(w) {
        if (w.native) return;
        try {
            history.replaceState(null, "", w.path);
        } catch {
            // keep going
        }
    }

    /* --------------------------------------------------- windows */

    function setTitle(w, title, icon) {
        w.title = title || w.title;
        if (icon) w.icon = icon;
        w.titleText.textContent = w.taskText.textContent = w.title;
        paintIcon(w.titleIcon, w.icon);
        paintIcon(w.taskIcon, w.icon);
        w.task.title = w.title;
        w.node.setAttribute("aria-label", w.title);
        if (w.node.classList.contains("focus")) document.title = `${w.title} · ${data.hotel}`;
    }

    function place(w) {
        const n = w.node;
        if (w.max || narrow()) {
            n.classList.add("max");
            for (const key of ["left", "top", "width", "height"]) n.style.removeProperty(key);
            queueMicrotask(syncWaves);
            return;
        }
        n.classList.remove("max");
        queueMicrotask(syncWaves);
        const W = layer.clientWidth;
        const H = layer.clientHeight;
        if (w.snap && ZONES[w.snap]) {
            Object.assign(w, zoneRect(w.snap));
            n.classList.add("snapped");
        } else {
            n.classList.remove("snapped");
            w.snap = null;
        }
        w.w = clamp(w.w, 360, W);
        w.h = clamp(w.h, 240, H);
        w.x = clamp(w.x, -w.w + 120, W - 120);
        w.y = clamp(w.y, 0, H - 40);
        Object.assign(n.style, { left: w.x + "px", top: w.y + "px", width: w.w + "px", height: w.h + "px" });
    }

    function focus(w) {
        closeStart();
        for (const other of wins.values()) {
            other.node.classList.toggle("focus", other === w);
            other.task.classList.toggle("on", other === w);
        }
        w.node.style.zIndex = String(++z);
        if (w.pending) {
            w.frame.src = embedUrl(w.path);
            w.pending = false;
        }
        document.title = `${w.title} · ${data.hotel}`;
        addressOf(w);
        persist();
    }

    function topWindow() {
        return [...wins.values()].filter((w) => !w.min).sort((a, b) => Number(b.node.style.zIndex) - Number(a.node.style.zIndex))[0] || null;
    }

    function snapTo(w, zone) {
        if (zone === "MAX") {
            if (!w.max && !w.snap) w.restore = { w: w.w, h: w.h, x: w.x, y: w.y };
            w.snap = null;
            w.max = true;
            place(w);
            persist();
            return;
        }
        if (zone === null) {
            const r = w.restore || { w: 960, h: 640, x: 140, y: 40 };
            w.max = false;
            w.snap = null;
            Object.assign(w, { w: r.w, h: r.h, x: r.x ?? 140, y: r.y ?? 40 });
            place(w);
            persist();
            return;
        }
        if (!w.max && !w.snap) w.restore = { w: w.w, h: w.h, x: w.x, y: w.y };
        w.max = false;
        w.snap = zone;
        w.min = false;
        w.node.hidden = false;
        place(w);
        focus(w);
        assist(zone);
    }

    /** After one window snaps, the other open windows are offered for the space that's left. */
    function assist(zone) {
        document.querySelector(".los-assist")?.remove();
        const layout = LAYOUTS.find((l) => l.includes(zone));
        if (!layout) return;
        const free = layout.filter((z0) => z0 !== zone && ![...wins.values()].some((o) => o.snap === z0 && !o.min));
        const others = [...wins.values()].filter((o) => !o.snap && !o.max && !o.min);
        if (!free.length || !others.length) return;
        const target = free[0];
        const r = zoneRect(target);
        const box = el("div", "los-assist", { role: "dialog", "aria-label": S.pickWindow });
        Object.assign(box.style, { left: r.x + "px", top: r.y + "px", width: r.w + "px", height: r.h + "px" });
        const row = el("div", "los-assist-row");
        for (const other of others) {
            const card = el("button", "los-assist-card", { type: "button" }, paintIcon(el("span", "", { "aria-hidden": "true" }), other.icon), el("b", "", { text: other.title }));
            card.addEventListener("click", () => {
                box.remove();
                snapTo(other, target);
            });
            row.append(card);
        }
        box.append(el("p", "", { text: S.pickWindow }), row);
        layer.append(box);
        const away = (event) => {
            if (!box.contains(event.target)) {
                box.remove();
                document.removeEventListener("pointerdown", away, true);
            }
        };
        setTimeout(() => document.addEventListener("pointerdown", away, true), 0);
    }

    /** The little layout picker that shows up when you rest the pointer on maximize. */
    function layoutFlyout(w, anchor) {
        document.querySelector(".los-flyout")?.remove();
        const fly = el("div", "los-flyout", { role: "menu" });
        for (const layout of LAYOUTS) {
            const cell = el("div", "los-fly-layout");
            for (const zone of layout) {
                const [a, b, c, d] = ZONES[zone];
                const spot = el("button", "los-fly-zone", { type: "button", "aria-label": "Snap " + zone, title: "Snap here" });
                Object.assign(spot.style, { left: a * 100 + "%", top: b * 100 + "%", width: c * 100 + "%", height: d * 100 + "%" });
                spot.addEventListener("click", () => {
                    fly.remove();
                    snapTo(w, zone);
                });
                cell.append(spot);
            }
            fly.append(cell);
        }
        const rect = anchor.getBoundingClientRect();
        fly.style.top = rect.bottom + 4 + "px";
        fly.style.left = Math.max(8, rect.right - 190) + "px";
        document.body.append(fly);
        fly.addEventListener("pointerleave", () => fly.remove());
    }

    /**
     * Builds the frame of a window around a body. `node` is passed for the window the server already rendered.
     * Kinds: "inline" (the page in the desktop itself), "frame" (a page in an iframe) and "native" (part of the desktop).
     */
    function makeWindow({ path, kind, node, body, title, icon, saved }) {
        const W = layer.clientWidth;
        const H = layer.clientHeight;
        const w = {
            id: ++seq,
            path,
            kind,
            native: kind === "native",
            title: title || metaFor(path).title || data.hotel,
            icon: icon || metaFor(path).icon,
            w: Math.min(1040, W - 160),
            h: Math.min(700, H - 40),
            x: Math.min(150 + (cascade % 6) * 28, Math.max(16, W - 420)),
            y: 24 + (cascade % 6) * 28,
            max: false,
            min: false,
            snap: null,
        };
        cascade++;

        if (saved) Object.assign(w, { x: saved.x, y: saved.y, w: saved.w, h: saved.h, max: saved.max, min: saved.min, snap: saved.snap });
        else if (!w.native) {
            w.restore = { w: w.w, h: w.h, x: w.x, y: w.y };
            w.max = true; // pages open full size; resize them from there
        }

        if (!node) {
            node = el("section", "los-win", { role: "dialog" }, el("header", "los-win-bar", {}, el("div", "los-win-title", {}, el("i", "", { "aria-hidden": "true" }), el("span")), el("div", "los-win-btns")), body);
            layer.append(node);
        }

        w.node = node;
        w.body = node.querySelector(".los-win-body");
        w.titleIcon = node.querySelector(".los-win-title i");
        w.titleText = node.querySelector(".los-win-title span");
        w.taskIcon = el("i", "", { "aria-hidden": "true" });
        w.taskText = el("span");
        w.task = el("button", "los-task", { type: "button" }, w.taskIcon, w.taskText);
        tasksBox.append(w.task);

        const btnMin = el("button", "los-win-btn", { type: "button", "aria-label": S.minimize, title: S.minimize, text: "–" });
        const btnMax = el("button", "los-win-btn", { type: "button", "aria-label": S.maximize, title: S.maximize, text: "▢" });
        const btnClose = el("button", "los-win-btn close", { type: "button", "aria-label": S.close, title: S.close, text: "✕" });
        node.querySelector(".los-win-btns").replaceChildren(btnMin, btnMax, btnClose);

        wins.set(w.id, w);
        setTitle(w, w.title, w.icon);

        const minimize = () => {
            w.min = true;
            node.hidden = true;
            node.classList.remove("focus");
            w.task.classList.remove("on");
            const top = topWindow();
            if (top) focus(top);
            else persist();
        };
        const close = () => {
            node.remove();
            w.task.remove();
            wins.delete(w.id);
            const top = topWindow();
            if (top) focus(top);
            else {
                document.title = data.hotel;
                persist();
            }
        };
        const toggleMax = () => {
            snapTo(w, w.max ? null : "MAX");
            focus(w);
        };
        w.close = close;

        btnMin.addEventListener("click", minimize);
        btnMax.addEventListener("click", toggleMax);
        btnClose.addEventListener("click", close);
        w.task.addEventListener("click", () => {
            if (w.min) {
                w.min = false;
                node.hidden = false;
                focus(w);
            } else if (node.classList.contains("focus")) minimize();
            else focus(w);
        });
        node.addEventListener("pointerdown", () => {
            if (!node.classList.contains("focus")) focus(w);
        }, true);
        node.querySelector(".los-win-title").addEventListener("dblclick", toggleMax);

        // drag by the title bar; touching an edge snaps, like Windows 11
        node.querySelector(".los-win-bar").addEventListener("pointerdown", (event) => {
            if (event.target.closest("button") || event.button !== 0 || narrow()) return;
            const bar = event.currentTarget;
            bar.setPointerCapture(event.pointerId);
            const sx = event.clientX;
            const sy = event.clientY;
            let ox = w.x;
            let oy = w.y;
            let started = false;
            let target = null;
            node.classList.add("dragging");
            const edge = (ev) => {
                const r = layer.getBoundingClientRect();
                const x = ev.clientX - r.left;
                const y = ev.clientY - r.top;
                if (y <= 6) return x <= 60 ? "TL" : x >= r.width - 60 ? "TR" : "MAX";
                if (x <= 6) return y <= 60 ? "TL" : y >= r.height - 60 ? "BL" : "L";
                if (x >= r.width - 6) return y <= 60 ? "TR" : y >= r.height - 60 ? "BR" : "R";
                return null;
            };
            const move = (ev) => {
                if (!started) {
                    if (Math.hypot(ev.clientX - sx, ev.clientY - sy) < 5) return;
                    started = true;
                    if (w.max || w.snap) {
                        // pulling a full-size window off the top brings it back to its own size under the pointer
                        const was = w.restore || { w: 960, h: 640 };
                        const frac = (sx - (layer.getBoundingClientRect().left + (w.max ? 0 : w.x))) / Math.max(1, w.max ? layer.clientWidth : w.w);
                        w.max = false;
                        w.snap = null;
                        w.w = was.w;
                        w.h = was.h;
                        w.x = ev.clientX - was.w * clamp(frac, 0.1, 0.9);
                        w.y = 0;
                        ox = w.x;
                        oy = w.y;
                        place(w);
                    }
                }
                w.x = ox + ev.clientX - sx;
                w.y = oy + ev.clientY - sy;
                w.restore = { w: w.w, h: w.h, x: w.x, y: w.y };
                place(w);
                target = edge(ev);
                showPreview(target);
            };
            const up = () => {
                bar.removeEventListener("pointermove", move);
                bar.removeEventListener("pointerup", up);
                bar.removeEventListener("pointercancel", up);
                node.classList.remove("dragging");
                showPreview(null);
                if (started && target) snapTo(w, target);
                persist();
            };
            bar.addEventListener("pointermove", move);
            bar.addEventListener("pointerup", up);
            bar.addEventListener("pointercancel", up);
        });

        let flyTimer = 0;
        btnMax.addEventListener("pointerenter", () => {
            if (!narrow()) flyTimer = setTimeout(() => layoutFlyout(w, btnMax), 450);
        });
        btnMax.addEventListener("pointerleave", () => clearTimeout(flyTimer));

        node.addEventListener("keydown", (event) => {
            if (!(event.ctrlKey && event.altKey)) return;
            const zone = { ArrowLeft: "L", ArrowRight: "R", ArrowUp: "MAX" }[event.key];
            if (zone) {
                event.preventDefault();
                snapTo(w, zone);
            } else if (event.key === "ArrowDown") {
                event.preventDefault();
                snapTo(w, null);
            }
        });

        // resize from the corner
        const grip = el("div", "los-win-grip", { "aria-hidden": "true" });
        node.append(grip);
        grip.addEventListener("pointerdown", (event) => {
            if (w.max) return;
            event.preventDefault();
            grip.setPointerCapture(event.pointerId);
            const sx = event.clientX;
            const sy = event.clientY;
            const ow = w.w;
            const oh = w.h;
            if (w.snap) {
                w.snap = null;
                w.restore = null;
            }
            node.classList.add("dragging");
            const move = (ev) => {
                w.w = ow + ev.clientX - sx;
                w.h = oh + ev.clientY - sy;
                place(w);
            };
            const up = () => {
                grip.removeEventListener("pointermove", move);
                grip.removeEventListener("pointerup", up);
                grip.removeEventListener("pointercancel", up);
                node.classList.remove("dragging");
                persist();
            };
            grip.addEventListener("pointermove", move);
            grip.addEventListener("pointerup", up);
            grip.addEventListener("pointercancel", up);
        });

        place(w);
        if (w.min) node.hidden = true;
        return w;
    }

    /** Puts a page in a window's frame, turning the desktop's own first window into a framed one when it moves on. */
    function frameWindow(w, path, defer) {
        if (!w.frame) {
            w.frame = el("iframe", "los-frame", { title: w.title, referrerpolicy: "same-origin", allow: "fullscreen; clipboard-write; autoplay" });
            w.body.className = "los-win-body los-page";
            w.body.replaceChildren(w.frame);
            w.kind = "frame";
            watchFrame(w);
        }
        w.path = path;
        if (defer) w.pending = true;
        else w.frame.src = embedUrl(path);
    }

    /** A window follows the page inside it: its title, icon and address change as you move around. */
    function watchFrame(w) {
        w.frame.addEventListener("load", () => {
            let doc;
            try {
                doc = w.frame.contentDocument;
                if (!doc || w.frame.contentWindow.location.href === "about:blank") return;
            } catch {
                return; // somewhere else entirely; leave it be
            }

            // signed in or out inside a window: the whole desktop needs to know, so start it again on that page
            if (doc.documentElement.dataset.losAuth !== undefined && doc.documentElement.dataset.losAuth !== document.documentElement.dataset.losAuth) {
                location.href = cleanPath(w.frame.contentWindow.location.href);
                return;
            }

            if (wavesAt === w) tellFrame(w, true);

            const path = cleanPath(w.frame.contentWindow.location.href);
            w.path = path;
            const meta = metaFor(path);
            const pageTitle = String(doc.title || "").split(" - ")[0].trim();
            setTitle(w, meta.title || pageTitle || w.title, meta.icon);
            if (w.node.classList.contains("focus")) addressOf(w);
            persist();

            doc.addEventListener("pointerdown", () => {
                closeStart();
                if (!w.node.classList.contains("focus")) focus(w);
            }, true);
        });
    }

    /** Opens a page, or brings forward the window that already shows it. */
    function open(path, options = {}) {
        if (path === "os:personalize") return openNative("os:personalize", personalize);
        if (path === "os:signin") return openNative("os:signin", signIn);

        const have = [...wins.values()].find((w) => w.path === path);
        if (have && !options.fresh) {
            have.min = false;
            have.node.hidden = false;
            focus(have);
            return have;
        }

        const meta = metaFor(path);
        const w = makeWindow({ path, kind: "frame", body: el("div", "los-win-body los-page"), title: meta.title, icon: meta.icon, saved: options.saved });
        frameWindow(w, path, options.defer);
        if (isClient(path)) {
            w.max = true;
            place(w);
        }
        if (!options.defer) focus(w);
        else w.node.style.zIndex = String(options.saved?.z || ++z);
        return w;
    }

    function openNative(key, build) {
        const have = [...wins.values()].find((w) => w.path === key);
        if (have) {
            have.min = false;
            have.node.hidden = false;
            focus(have);
            return have;
        }
        const body = el("div", "los-win-body los-native");
        const w = makeWindow({ path: key, kind: "native", body, title: pages[key].title, icon: pages[key].icon });
        w.w = Math.min(560, layer.clientWidth - 40);
        w.h = Math.min(key === "os:signin" ? 600 : 520, layer.clientHeight - 40);
        w.x = Math.max(16, (layer.clientWidth - w.w) / 2);
        w.y = Math.max(16, (layer.clientHeight - w.h) / 3);
        build(body);
        place(w);
        focus(w);
        return w;
    }

    /* ------------------------------------------------ personalize */

    const WALLS = [
        ["default", "Default", ""],
        ["ocean", "Ocean", "linear-gradient(135deg, #0a3a86 0%, #0e55b8 45%, #1b86e0 100%)"],
        ["dusk", "Dusk", "linear-gradient(135deg, #2b1055 0%, #7597de 100%)"],
        ["sunset", "Sunset", "linear-gradient(135deg, #42275a 0%, #a8456b 55%, #f09a5b 100%)"],
        ["forest", "Forest", "linear-gradient(135deg, #0b3d2e 0%, #1c7a52 60%, #6bbf8a 100%)"],
        ["graphite", "Graphite", "linear-gradient(135deg, #16181d 0%, #2b2f38 100%)"],
        ["island", "Hotel island", "url(/assets/images/default-background.png) center / cover no-repeat, linear-gradient(180deg, #0b2a4a, #0f1724)"],
        ["aurora", "Aurora", "radial-gradient(900px 600px at 20% 10%, #2dd4bf55, transparent 60%), radial-gradient(900px 600px at 85% 80%, #8b5cf655, transparent 60%), linear-gradient(135deg, #0b1220, #101a33)"],
    ];
    let prefs = { wall: "default", waves: true };
    try {
        const saved = JSON.parse(store.get(PREFS) || "null");
        if (saved) prefs = { wall: WALLS.some((x) => x[0] === saved.wall) || /^#[0-9a-f]{6}$/i.test(saved.wall || "") ? saved.wall : "default", waves: saved.waves !== false };
    } catch {
        // defaults
    }

    function applyPrefs() {
        const css = /^#[0-9a-f]{6}$/i.test(prefs.wall) ? prefs.wall : (WALLS.find((x) => x[0] === prefs.wall) || [])[2] || "";
        if (css) document.body.style.setProperty("--wall", css);
        else document.body.style.removeProperty("--wall");

        // desktop labels follow the wallpaper, not the theme: a dark wallpaper needs light text even in light mode
        let tone = "";
        if (css) {
            tone = "dark";
            if (/^#/.test(prefs.wall)) {
                const n = parseInt(prefs.wall.slice(1), 16);
                const lum = (0.299 * (n >> 16) + 0.587 * ((n >> 8) & 255) + 0.114 * (n & 255)) / 255;
                tone = lum > 0.6 ? "light" : "dark";
            }
        }
        document.body.classList.toggle("los-wall-art", prefs.wall === "island");
        document.body.classList.toggle("los-wall-dark", tone === "dark");
        document.body.classList.toggle("los-wall-light", tone === "light");
        wavesReady = true;
        wavesAt = undefined;
        syncWaves();
    }

    function savePrefs(patch) {
        prefs = { ...prefs, ...patch };
        store.set(PREFS, JSON.stringify(prefs));
        applyPrefs();
    }

    function personalize(host) {
        const box = el("div", "pz");
        const draw = () => {
            const swatches = WALLS.map(([id, name, css]) => {
                const b = el("button", "pz-wall" + (prefs.wall === id ? " on" : ""), { type: "button", title: name, "aria-label": name }, el("span", "", { text: name }));
                if (css) b.style.background = css;
                b.addEventListener("click", () => {
                    savePrefs({ wall: id });
                    draw();
                });
                return b;
            });
            const color = el("input", "", { type: "color", "aria-label": S.anyColor, value: /^#/.test(prefs.wall) ? prefs.wall : "#1b2a4a" });
            color.addEventListener("change", () => {
                savePrefs({ wall: color.value });
                draw();
            });
            const waves = el("input", "", { type: "checkbox" });
            waves.checked = prefs.waves;
            waves.addEventListener("change", () => {
                savePrefs({ waves: waves.checked });
                draw();
            });
            const themeBtn = el("button", "los-btn", { type: "button", text: Theme.current() === "light" ? S.toDark : S.toLight });
            themeBtn.addEventListener("click", () => {
                Theme.toggle();
                draw();
            });
            box.replaceChildren(
                el("h3", "", { text: S.wallpaper }),
                el("div", "pz-row", {}, ...swatches, el("label", "pz-color" + (/^#/.test(prefs.wall) ? " on" : ""), {}, color, el("span", "", { text: S.anyColor }))),
                el("label", "pz-waves", {}, waves, el("span", "", {}, el("b", "", { text: S.waves }), el("small", "", { text: S.wavesHint }))),
                el("h3", "", { text: S.theme }),
                el("div", "pz-row", {}, themeBtn),
                el("p", "pz-note", { text: S.savedHere }),
            );
        };
        draw();
        host.append(box);
    }

    function signIn(host) {
        const tpl = document.getElementById("los-signin");
        if (tpl) host.append(tpl.content.cloneNode(true));
        window.Alpine?.initTree?.(host);
        host.querySelectorAll("[data-los-open]").forEach((a) =>
            a.addEventListener("click", (event) => {
                event.preventDefault();
                open(cleanPath(a.href));
            }),
        );
        setTimeout(() => host.querySelector('input[name="username"]')?.focus(), 50);
    }

    /* ------------------------------------------------ desktop icons */

    function drawIcons() {
        const entries = [
            ...data.groups.filter((g) => g.pages.length).map((g) => ({ label: g.label, icon: g.icon, run: () => open(g.pages[0].path), hint: g.pages.map((p) => p.title).join(", ") })),
            ...(data.user ? [] : [{ label: S.signIn, icon: "🔑", run: () => open("os:signin") }]),
            { label: S.personalize, icon: "🎨", run: () => open("os:personalize") },
            ...data.links.map((l) => ({ label: l.title, icon: l.icon, href: l.url })),
        ];
        iconsBox.replaceChildren();
        for (const entry of entries) {
            const art = paintIcon(el("span", "los-icon-art", { "aria-hidden": "true" }), entry.icon);
            const name = el("span", "los-icon-name", { text: entry.label });
            if (entry.href) {
                iconsBox.append(el("a", "los-icon", { href: entry.href, target: "_blank", rel: "noopener noreferrer", title: entry.label }, art, name));
                continue;
            }
            const icon = el("button", "los-icon", { type: "button", title: entry.hint || entry.label }, art, name);
            let last = 0;
            icon.addEventListener("click", () => {
                iconsBox.querySelectorAll(".sel").forEach((n) => n.classList.remove("sel"));
                icon.classList.add("sel");
                // double-click like a real desktop; a touch screen opens on the first tap
                if (Date.now() - last < 450 || window.matchMedia("(pointer: coarse)").matches) entry.run();
                last = Date.now();
            });
            icon.addEventListener("keydown", (event) => {
                if (event.key === "Enter") entry.run();
            });
            iconsBox.append(icon);
        }
    }

    /* ------------------------------------------------ start menu */

    const tasksBox = el("div", "los-tasks");

    function startMenu() {
        const menu = el("div", "los-start", { hidden: "" });
        const rail = el("div", "los-start-rail");
        const results = el("div", "los-start-list");
        const tiles = el("div", "los-start-tiles");
        const defaults = Object.keys(pages).filter((p) => p.startsWith("/") && !isClient(p)).slice(0, 8);
        const loadPins = () => {
            try {
                const saved = JSON.parse(store.get(PINS) || "null");
                if (Array.isArray(saved)) return saved.filter((p) => pages[p]);
            } catch {
                // defaults
            }
            return defaults;
        };
        let pinned = loadPins();
        let lastQuery = "";
        let dragFrom = -1;
        const hues = ["#0a64c8", "#8a3ffc", "#1a8f5a", "#d4741a", "#c4392b", "#0f7b8a", "#6a4fd0"];
        const savePins = () => {
            store.set(PINS, JSON.stringify(pinned));
            drawTiles();
            paint(lastQuery);
        };

        function drawTiles() {
            const reset = el("button", "los-pin-reset", { type: "button", text: S.reset });
            reset.addEventListener("click", () => {
                pinned = defaults;
                savePins();
            });
            const wraps = pinned.map((path, i) => {
                const tile = el("button", "los-tile", { type: "button" }, paintIcon(el("span", "los-tile-art", { "aria-hidden": "true" }), pages[path].icon), el("span", "", { text: pages[path].title }));
                tile.style.setProperty("--tile", hues[i % hues.length]);
                tile.addEventListener("click", () => {
                    close();
                    open(path);
                });
                const unpin = el("button", "los-tile-x", { type: "button", "aria-label": "Unpin " + pages[path].title, title: "Unpin", text: "✕" });
                unpin.addEventListener("click", () => {
                    pinned = pinned.filter((p) => p !== path);
                    savePins();
                });
                const wrap = el("div", "los-tile-wrap", { draggable: "true" }, tile, unpin);
                wrap.addEventListener("dragstart", (event) => {
                    dragFrom = i;
                    event.dataTransfer.effectAllowed = "move";
                    wrap.classList.add("drag");
                });
                wrap.addEventListener("dragend", () => wrap.classList.remove("drag"));
                wrap.addEventListener("dragover", (event) => event.preventDefault());
                wrap.addEventListener("drop", (event) => {
                    event.preventDefault();
                    if (dragFrom < 0 || dragFrom === i) return;
                    const [moved] = pinned.splice(dragFrom, 1);
                    pinned.splice(i, 0, moved);
                    dragFrom = -1;
                    savePins();
                });
                return wrap;
            });
            tiles.replaceChildren(
                el("div", "los-start-brand", {}, el("b", "", { text: "LucadoOS" }), el("small", "", { text: data.hotel })),
                el("div", "los-start-group los-pins-head", {}, el("span", "", { text: S.pinned }), el("small", "", { text: S.dragHint }), reset),
                ...(wraps.length ? wraps : [el("p", "los-start-none", { text: S.nothingPinned })]),
            );
        }

        function paint(query) {
            lastQuery = query = (query || "").trim().toLowerCase();
            results.replaceChildren();
            for (const group of data.groups) {
                const hits = group.pages.filter((p) => !query || p.title.toLowerCase().includes(query) || group.label.toLowerCase().includes(query));
                if (!hits.length) continue;
                results.append(el("div", "los-start-group", { text: group.label }));
                for (const page of hits) {
                    const item = el("button", "los-start-item", { type: "button" }, paintIcon(el("i", "", { "aria-hidden": "true" }), page.icon), el("span", "", { text: page.title }));
                    item.addEventListener("click", () => {
                        close();
                        open(page.path);
                    });
                    const on = pinned.includes(page.path);
                    const pin = el("button", "los-pin" + (on ? " on" : ""), { type: "button", title: on ? "Unpin from Start" : "Pin to Start", "aria-label": (on ? "Unpin " : "Pin ") + page.title, text: on ? "★" : "☆" });
                    pin.addEventListener("click", () => {
                        pinned = on ? pinned.filter((p) => p !== page.path) : [...pinned, page.path];
                        savePins();
                    });
                    results.append(el("div", "los-start-row", {}, item, pin));
                }
            }
            const links = data.links.filter((l) => !query || l.title.toLowerCase().includes(query));
            if (links.length) {
                results.append(el("div", "los-start-group", { text: S.links }));
                for (const link of links) results.append(el("a", "los-start-item", { href: link.url, target: "_blank", rel: "noopener noreferrer" }, paintIcon(el("i", "", { "aria-hidden": "true" }), link.icon), el("span", "", { text: link.title })));
            }
            if (!results.children.length) results.append(el("p", "los-start-none", { text: S.noMatch }));
        }

        const railButton = (glyph, label, run) => {
            const b = el("button", "los-rail-btn", { type: "button", title: label, "aria-label": label, text: glyph });
            b.addEventListener("click", run);
            return b;
        };
        rail.append(
            ...[
                railButton("🎨", S.personalize, () => {
                    close();
                    open("os:personalize");
                }),
                data.user
                    ? railButton("⎋", S.logout, () => document.getElementById("logout-form")?.submit())
                    : railButton("🔑", S.signIn, () => {
                          close();
                          open("os:signin");
                      }),
            ],
        );

        menu.append(rail, results, tiles);
        drawTiles();
        paint("");

        const btn = el("button", "los-startbtn", { type: "button", "aria-label": S.start, "aria-expanded": "false" }, el("span", "los-winlogo", { "aria-hidden": "true" }, el("i"), el("i"), el("i"), el("i")));
        const search = el("input", "", { type: "text", placeholder: S.search, "aria-label": S.search, autocomplete: "off", spellcheck: "false" });
        const box = el("label", "los-searchbox", {}, el("span", "", { "aria-hidden": "true", text: "⌕" }), search);
        const close = () => {
            menu.hidden = true;
            btn.setAttribute("aria-expanded", "false");
        };
        closeStart = close;
        const show = (query) => {
            menu.hidden = false;
            btn.setAttribute("aria-expanded", "true");
            paint(query);
        };
        btn.addEventListener("click", (event) => {
            event.stopPropagation();
            if (menu.hidden) {
                search.value = "";
                show("");
            } else close();
        });
        search.addEventListener("focus", () => show(search.value));
        search.addEventListener("input", () => show(search.value));
        search.addEventListener("keydown", (event) => {
            if (event.key === "Enter") {
                results.querySelector(".los-start-item")?.click();
                search.value = "";
                search.blur();
            }
        });
        document.addEventListener("pointerdown", (event) => {
            if (!menu.hidden && !menu.contains(event.target) && !btn.contains(event.target) && !box.contains(event.target)) close();
        });
        document.addEventListener("keydown", (event) => {
            if (event.key === "Escape") close();
        });
        return { menu, btn, box };
    }

    /* ------------------------------------------------------ taskbar */

    function tray() {
        const box = el("div", "los-tray");
        if (data.user) {
            const fmt = (n) => Number(n).toLocaleString("en-US");
            const coins = {};
            const coin = (kind) => (coins[kind] = el("b", "", { text: fmt(data.user[kind]) }));
            box.append(
                el("span", "los-purse", { title: "Credits · Duckets · Diamonds" },
                    el("span", "los-coin credits", {}, el("i", "", { "aria-hidden": "true" }), coin("credits")),
                    el("span", "los-coin duckets", {}, el("i", "", { "aria-hidden": "true" }), coin("duckets")),
                    el("span", "los-coin diamonds", {}, el("i", "", { "aria-hidden": "true" }), coin("diamonds")),
                ),
            );
            // Spending in the game shows up here without a page reload.
            const refresh = () => {
                if (document.hidden) return;
                fetch(data.user.balance, { headers: { Accept: "application/json" }, credentials: "same-origin" })
                    .then((res) => (res.ok ? res.json() : null))
                    .then((balance) => {
                        if (!balance) return;
                        for (const kind of Object.keys(coins)) {
                            if (kind in balance) coins[kind].textContent = fmt(balance[kind]);
                        }
                        if ("unread" in balance) setUnread(balance.unread);
                    })
                    .catch(() => {});
            };
            const bell = el("button", "los-traybtn los-bell", { type: "button", title: "Notifications" }, el("span", "", { text: "🔔", "aria-hidden": "true" }));
            const badge = el("b", "los-badge");
            bell.append(badge);
            const setUnread = (n) => {
                badge.textContent = n > 99 ? "99+" : String(n);
                badge.hidden = !n;
                bell.title = n ? `Notifications (${n} new)` : "Notifications";
            };
            setUnread(Number(data.user.unread || 0));
            bell.addEventListener("click", () => open(data.user.notifications));
            box.append(bell);
            setInterval(refresh, 10000);
            document.addEventListener("visibilitychange", refresh);
            window.addEventListener("focus", refresh);
            const me = el("button", "los-who", { type: "button", title: data.user.name }, el("img", "", { src: data.user.head, alt: "", width: "27", height: "30" }), el("span", "", { text: data.user.name }));
            me.addEventListener("click", () => open(data.home));
            box.append(me);
        } else {
            const signInBtn = el("button", "los-btn", { type: "button", text: S.signIn });
            signInBtn.addEventListener("click", () => open("os:signin"));
            box.append(signInBtn);
        }

        const themeBtn = el("button", "los-traybtn", { type: "button" });
        const paint = () => {
            const light = Theme.current() === "light";
            themeBtn.textContent = light ? "☾" : "☀";
            themeBtn.title = light ? "Switch to the dark theme" : "Switch to the light theme";
            themeBtn.setAttribute("aria-label", themeBtn.title);
        };
        themeBtn.addEventListener("click", () => Theme.toggle());
        window.addEventListener("los-theme", paint);
        paint();

        const clock = el("div", "los-clock");
        const tick = () => {
            const now = new Date();
            clock.replaceChildren(el("span", "", { text: now.toLocaleTimeString("en-US", { hour: "numeric", minute: "2-digit" }) }), el("span", "", { text: now.toLocaleDateString("en-US") }));
        };
        tick();
        setInterval(tick, 20_000);

        box.append(themeBtn, clock);
        return box;
    }

    // light or dark reaches every open page
    window.addEventListener("los-theme", (event) => {
        for (const w of wins.values()) {
            try {
                const root = w.frame?.contentDocument?.documentElement;
                if (!root) continue;
                root.dataset.theme = event.detail;
                root.classList.toggle("dark", event.detail === "dark");
            } catch {
                // not loaded yet; it reads the saved theme itself
            }
        }
    });

    /* ------------------------------------------------- links */

    /** Links in the desktop's own page window keep it a window: the next page loads in place instead of reloading the desktop. */
    function followLinks(w) {
        w.body.addEventListener("click", (event) => {
            const a = event.target.closest("a[href]");
            if (!a || event.defaultPrevented || event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return;
            if ((a.target && a.target !== "_self") || a.hasAttribute("download")) return;
            const url = new URL(a.href, location.href);
            if (!sameOrigin(url) || url.protocol.startsWith("javascript")) return;
            if (url.pathname === location.pathname && url.search === location.search && url.hash) return; // a jump within the page
            event.preventDefault();
            const path = cleanPath(url.href);
            if (isClient(path)) open(path);
            else frameWindow(w, path);
        });
    }

    // pages in frames ask us to open the hotel client in its own window
    window.addEventListener("message", (event) => {
        if (event.origin !== location.origin || !event.data || event.data.los !== "open") return;
        open(cleanPath(event.data.path));
    });

    /* ------------------------------------------------- start up */

    const { menu, btn, box } = startMenu();
    desk.append(menu, el("footer", "los-taskbar", {}, btn, box, tasksBox, tray()));
    drawIcons();
    applyPrefs();

    const mainNode = desk.querySelector(".los-main");
    const mainPath = mainNode.dataset.losPath;
    const mainMeta = metaFor(mainPath);
    const main = makeWindow({ path: mainPath, kind: "inline", node: mainNode, title: mainMeta.title || mainNode.dataset.losTitle, icon: mainMeta.icon });
    main.max = true;
    place(main);
    followLinks(main);

    // bring back the windows that were open last time; they load the first time you reach for them
    let saved = [];
    try {
        saved = JSON.parse(store.get(SAVE) || "[]");
    } catch {
        saved = [];
    }
    const savedMain = saved.find((s) => s.path === mainPath);
    if (savedMain && !narrow()) {
        Object.assign(main, { x: savedMain.x, y: savedMain.y, w: savedMain.w, h: savedMain.h, max: savedMain.max, snap: savedMain.snap, min: false });
        place(main);
    }
    for (const s of saved.filter((x) => x.path !== mainPath && typeof x.path === "string" && x.path.startsWith("/")).sort((a, b) => (a.z || 0) - (b.z || 0)).slice(0, 8)) {
        open(s.path, { saved: s, defer: true, fresh: true });
    }
    // the page you just asked for always lands on top of the windows that came back
    z = Math.max(z, ...[...wins.values()].map((w) => Number(w.node.style.zIndex) || 0));
    focus(main);

    // Someone who came to sign up or reset a password should see that page, not the sign-in window on top of it.
    const onAccountPage = /^\/(register|forgot-password|reset-password)(\/|$)/.test(location.pathname);
    if (!data.user && (window.losOpenSignIn || (!onAccountPage && !store.get(SAVE + "-guest-seen")))) {
        store.set(SAVE + "-guest-seen", "1");
        open("os:signin");
    }

    let wasNarrow = narrow();
    window.addEventListener("resize", () => {
        const now = narrow();
        if (now !== wasNarrow) {
            wasNarrow = now;
            document.body.classList.toggle("los-narrow", now);
        }
        for (const w of wins.values()) place(w);
    });

    window.LucadoOS = { open, theme: Theme };
}

/** Inside a window: links to the hotel client open it in a window of its own rather than squeezing it in here. */
function embedded() {
    window.addEventListener("message", (event) => {
        if (event.origin !== location.origin || event.source !== window.parent || event.data?.los !== "waves") return;
        if (event.data.on) Waves.start(document.body);
        else Waves.stop();
    });

    const clientPaths = ["/game/nitro", "/game/flash"];
    document.addEventListener("click", (event) => {
        const a = event.target.closest("a[href]");
        if (!a || event.button !== 0 || event.metaKey || event.ctrlKey) return;
        const url = new URL(a.href, location.href);
        if (!sameOrigin(url)) {
            if (!a.target) a.target = "_blank";
            return;
        }
        if (clientPaths.includes(url.pathname) && window.parent !== window) {
            event.preventDefault();
            window.parent.postMessage({ los: "open", path: url.pathname + url.search }, location.origin);
        }
    });
}

export default {
    init() {
        const start = () => {
            const data = document.getElementById("los-data");
            if (document.getElementById("los-desk") && data) desktop(JSON.parse(data.textContent));
            else if (document.documentElement.classList.contains("los-embed")) embedded();
        };
        if (document.readyState === "loading") document.addEventListener("DOMContentLoaded", start, { once: true });
        else start();
    },
};

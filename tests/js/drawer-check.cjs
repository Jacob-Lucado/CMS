// Runs the real badge drawer script against a small fake canvas. Usage: node drawer-check.cjs <drawer.js> <gif-reader.js> <fixtures dir>
const fs = require('fs');
const assert = require('assert');
const vm = require('vm');

const [drawerFile, readerFile, fixtures] = process.argv.slice(2);

class ImageData {
    constructor(a, b, c) {
        if (typeof a === 'number') { this.width = a; this.height = b; this.data = new Uint8ClampedArray(a * b * 4); } else { this.data = a; this.width = b; this.height = c; }
    }
}

let thumbs = 0;
class FakeCanvas {
    constructor() { this.width = 0; this.height = 0; this.pixels = new Uint8ClampedArray(0); this.ctx = null; }
    getContext() {
        const canvas = this;
        if (this.ctx) return this.ctx;
        this.ctx = {
            fillStyle: '#000000', imageSmoothingEnabled: true,
            clearRect(x, y, w, h) { canvas.ensure(); for (let j = y; j < y + h; j++) for (let i = x; i < x + w; i++) canvas.set(i, j, 0, 0, 0, 0); },
            fillRect(x, y, w, h) { canvas.ensure(); const n = parseInt(this.fillStyle.slice(1), 16); for (let j = y; j < y + h; j++) for (let i = x; i < x + w; i++) canvas.set(i, j, n >> 16, (n >> 8) & 255, n & 255, 255); },
            getImageData(x, y, w, h) { canvas.ensure(); const out = new ImageData(w, h); for (let j = 0; j < h; j++) for (let i = 0; i < w; i++) { const s = ((y + j) * canvas.width + x + i) * 4; out.data.set(canvas.pixels.subarray(s, s + 4), (j * w + i) * 4); } return out; },
            putImageData(img, x, y) { canvas.ensure(); for (let j = 0; j < img.height; j++) for (let i = 0; i < img.width; i++) { const s = (j * img.width + i) * 4; canvas.set(x + i, y + j, ...img.data.subarray(s, s + 4)); } },
            drawImage(src, dx, dy, dw, dh) {
                canvas.ensure(); src.ensure && src.ensure();
                const sw = src.width, sh = src.height; dw = dw || sw; dh = dh || sh;
                for (let j = 0; j < dh; j++) for (let i = 0; i < dw; i++) {
                    const s = (Math.floor(j * sh / dh) * sw + Math.floor(i * sw / dw)) * 4;
                    if (src.pixels[s + 3] > 0) canvas.set(dx + i, dy + j, src.pixels[s], src.pixels[s + 1], src.pixels[s + 2], src.pixels[s + 3]);
                }
            },
        };
        return this.ctx;
    }
    ensure() { if (this.pixels.length !== this.width * this.height * 4) this.pixels = new Uint8ClampedArray(this.width * this.height * 4); }
    set(x, y, r, g, b, a) { if (x < 0 || y < 0 || x >= this.width || y >= this.height) return; const i = (y * this.width + x) * 4; this.pixels[i] = r; this.pixels[i + 1] = g; this.pixels[i + 2] = b; this.pixels[i + 3] = a; }
    pixel(x, y) { this.ensure(); const i = (y * this.width + x) * 4; return Array.from(this.pixels.slice(i, i + 4)); }
    toDataURL() { return 'data:fake' + (++thumbs); }
    addEventListener() {}
}

const alerts = []; const gifs = [];
class GIF {
    constructor(options) { this.options = options; this.frames = []; this.handlers = {}; gifs.push(this); }
    addFrame(canvas, options) { this.frames.push({ pixels: Array.from(canvas.pixels), options }); }
    on(name, fn) { this.handlers[name] = fn; }
    render() { this.handlers.finished({ size: 1234 }); }
}

const reader = { exports: {} };
new Function('module', fs.readFileSync(readerFile, 'utf8'))(reader);

const timers = [];
const sandbox = {
    ImageData, GIF, GifReader: reader.exports, console,
    alert: (m) => alerts.push(m), confirm: () => true,
    clearTimeout: () => {}, setTimeout: (fn, ms) => { timers.push({ fn, ms }); return timers.length; }, clearInterval() {}, setInterval() { return 1; },
    Uint8Array, Uint8ClampedArray, Set, Object, Math, Number, JSON, Promise,
    document: { createElement: () => new FakeCanvas(), documentElement: { classList: { contains: () => false } } },
    translations: { invalid_file_type: 'bad type', missing_fields: 'missing', invalid_content: 'url', buy_confirmation: 'sure' },
    Date,
};
vm.createContext(sandbox);
const source = fs.readFileSync(drawerFile, 'utf8');
vm.runInContext(source, sandbox);

function makeDrawer() {
    const canvas = new FakeCanvas(); canvas.width = 40; canvas.height = 40; canvas.clientWidth = 640;
    const preview = new FakeCanvas(); preview.width = 40; preview.height = 40;
    const guide = { style: {} };
    const checker = { style: {} };
    const drawer = vm.runInContext('badgeDrawer', sandbox)({ cost: 150, currencyType: 'credits' });
    Object.assign(drawer, {
        $refs: { canvas, previewCanvas: preview, guide }, $watch() {}, $el: { querySelector: () => checker },
    });
    drawer.init();
    return { drawer, canvas, preview };
}

const sleep = () => new Promise((resolve) => setTimeout.constructor === Function ? resolve() : resolve());
async function settle() { for (let i = 0; i < 20; i++) await Promise.resolve(); await new Promise((r) => global.setTimeout(r, 20)); }

(async () => {
    // 1. drawing and validity
    let { drawer, canvas, preview } = makeDrawer();
    assert.strictEqual(drawer.frames.length, 1);
    assert.strictEqual(drawer.hasContent, false);
    drawer.color = '#FF0000'; drawer.paintCell(1, 1);
    assert.strictEqual(drawer.hasContent, true);
    drawer.badgeName = 'Fire'; drawer.badgeDescription = 'Hot';
    assert.strictEqual(drawer.isValid, true);
    assert.ok(drawer.frames[0].thumb.startsWith('data:fake'));

    // 2. copy a frame, draw on the copy, flip between frames
    drawer.addFrame(true);
    assert.strictEqual(drawer.frames.length, 2); assert.strictEqual(drawer.current, 1);
    assert.deepStrictEqual(canvas.pixel(1, 1), [255, 0, 0, 255]);
    drawer.color = '#0000FF'; drawer.paintCell(2, 2);
    drawer.selectFrame(0);
    assert.deepStrictEqual(canvas.pixel(2, 2), [0, 0, 0, 0]);
    assert.deepStrictEqual(canvas.pixel(1, 1), [255, 0, 0, 255]);
    drawer.selectFrame(1);
    assert.deepStrictEqual(canvas.pixel(2, 2), [0, 0, 255, 255]);
    assert.strictEqual(Object.keys(drawer.frames[0].history).length, 1);
    assert.strictEqual(Object.keys(drawer.colorHistory).length, 2);

    // 3. empty frame, move, delete
    drawer.addFrame(false);
    assert.strictEqual(drawer.frames.length, 3); assert.strictEqual(drawer.current, 2);
    assert.deepStrictEqual(canvas.pixel(1, 1), [0, 0, 0, 0]);
    drawer.moveFrame(-1);
    assert.strictEqual(drawer.current, 1);
    drawer.selectFrame(2);
    assert.deepStrictEqual(canvas.pixel(2, 2), [0, 0, 255, 255]); // the blue frame moved to the end
    drawer.removeFrame();
    assert.strictEqual(drawer.frames.length, 2);
    drawer.removeFrame(); drawer.removeFrame();
    assert.strictEqual(drawer.frames.length, 1); // never below one
    for (let i = 0; i < 40; i++) drawer.addFrame(true);
    assert.strictEqual(drawer.frames.length, 24); // capped

    // 4. playing the preview, and stopping when drawing
    drawer.togglePlay();
    assert.strictEqual(drawer.playing, true);
    drawer.paintCell(5, 5);
    assert.strictEqual(drawer.playing, false);

    // 5. export: every frame, unused transparent colour, safe delays
    ({ drawer, canvas } = makeDrawer());
    drawer.color = '#FF00FF'; drawer.paintCell(0, 0);       // uses the default placeholder colour
    drawer.addFrame(true); drawer.frames[1].delay = 5; drawer.frames[0].delay = 99999;
    await drawer.generateGifBlob();
    const g = gifs[gifs.length - 1];
    assert.strictEqual(g.frames.length, 2);
    assert.notStrictEqual(g.options.transparent, 0xFF00FF);
    assert.deepStrictEqual(g.frames.map((f) => f.options.delay), [2000, 50]);
    assert.ok(g.frames.every((f) => f.options.copy === true));
    assert.strictEqual(g.options.repeat, 0);
    // transparent area is filled with the placeholder colour, the drawn pixel stays
    const t = g.options.transparent;
    assert.deepStrictEqual(g.frames[0].pixels.slice(0, 4), [255, 0, 255, 255]);
    assert.deepStrictEqual(g.frames[0].pixels.slice(4, 8), [t >> 16, (t >> 8) & 255, t & 255, 255]);

    // 6. import an animated gif
    ({ drawer, canvas } = makeDrawer());
    const anim = fs.readFileSync(fixtures + '/anim.gif');
    let input = { target: { files: [{ type: 'image/gif', arrayBuffer: async () => anim.buffer.slice(anim.byteOffset, anim.byteOffset + anim.byteLength) }], value: 'x' } };
    drawer.importImage(input); await settle();
    assert.strictEqual(drawer.frames.length, 3);
    assert.deepStrictEqual(drawer.frames.map((f) => f.delay), [100, 200, 100]);
    assert.strictEqual(drawer.current, 0);
    assert.deepStrictEqual(canvas.pixel(0, 0), [255, 0, 0, 255]);     // frame 1 is red
    assert.deepStrictEqual(canvas.pixel(39, 39), [255, 0, 0, 255]);
    assert.strictEqual(Object.keys(drawer.frames[0].history).length, 1600);
    assert.ok(drawer.recentColors.includes('#FF0000') && drawer.recentColors.includes('#0000FF'));
    drawer.selectFrame(2);
    assert.deepStrictEqual(canvas.pixel(20, 20), [0, 0, 255, 255]);   // frame 3 is blue
    assert.strictEqual(input.target.value, '');
    assert.strictEqual(alerts.length, 0);

    // 7. a gif with too many frames is thinned, and the person is told
    const many = fs.readFileSync(fixtures + '/many.gif');
    input = { target: { files: [{ type: 'image/gif', arrayBuffer: async () => many.buffer.slice(many.byteOffset, many.byteOffset + many.byteLength) }], value: 'x' } };
    drawer.importImage(input); await settle();
    assert.strictEqual(drawer.frames.length, 24);
    assert.strictEqual(alerts.length, 1);

    // 8. broken and wrong files
    alerts.length = 0;
    input = { target: { files: [{ type: 'image/gif', arrayBuffer: async () => new Uint8Array([1, 2, 3, 4, 5, 6, 7, 8]).buffer }], value: 'x' } };
    drawer.importImage(input); await settle();
    assert.strictEqual(alerts.length, 1);
    assert.strictEqual(drawer.frames.length, 24); // nothing was replaced
    input = { target: { files: [{ type: 'image/jpeg' }], value: 'x' } };
    drawer.importImage(input);
    assert.deepStrictEqual(alerts.slice(-1), ['bad type']);

    // 9. clear all
    drawer.clearBoard();
    assert.strictEqual(drawer.frames.length, 1); assert.strictEqual(drawer.hasContent, false); assert.strictEqual(drawer.recentColors.length, 0);

    console.log('drawer ok');
})().catch((error) => { console.error(error); process.exit(1); });

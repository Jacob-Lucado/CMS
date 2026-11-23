// Usage: node gif-reader-check.cjs <gif-reader.js> <fixtures dir>
const fs = require('fs');
const [readerFile, fixtures] = process.argv.slice(2);
const assert = require('assert');
const m = { exports: {} }; new Function('module', fs.readFileSync(readerFile, 'utf8'))(m); const { read, sample } = m.exports;

const expected = JSON.parse(fs.readFileSync(fixtures + '/plain.json'));
for (const name of ['plain', 'interlaced']) {
    const g = read(fs.readFileSync(`${fixtures}/${name}.gif`));
    assert.strictEqual(g.width, 57); assert.strictEqual(g.height, 43); assert.strictEqual(g.frames.length, 1);
    let bad = 0;
    for (let i = 0; i < 57 * 43; i++) {
        if (g.frames[0].rgba[i * 4] !== expected[i * 3] || g.frames[0].rgba[i * 4 + 1] !== expected[i * 3 + 1] || g.frames[0].rgba[i * 4 + 2] !== expected[i * 3 + 2] || g.frames[0].rgba[i * 4 + 3] !== 255) bad++;
    }
    assert.strictEqual(bad, 0, `${name}: ${bad} wrong pixels`);
}
const a = read(fs.readFileSync(fixtures + '/anim.gif'));
assert.strictEqual(a.frames.length, 3);
assert.deepStrictEqual(a.frames.map(f => f.delay), [100, 200, 100]);
const px = (f, x, y) => Array.from(a.frames[f].rgba.slice((y * 4 + x) * 4, (y * 4 + x) * 4 + 4));
assert.deepStrictEqual(px(0, 0, 0), [255, 0, 0, 255]);
assert.deepStrictEqual(px(1, 1, 1), [0, 255, 0, 255]);   // green drawn
assert.deepStrictEqual(px(1, 2, 1), [255, 0, 0, 255]);   // transparent keeps the red underneath
assert.deepStrictEqual(px(1, 0, 0), [255, 0, 0, 255]);
assert.deepStrictEqual(px(2, 1, 1), [0, 0, 255, 255]);
assert.deepStrictEqual(px(2, 0, 0), [0, 0, 255, 255]);
const s = sample(Array.from({ length: 10 }, (_, i) => ({ rgba: new Uint8ClampedArray(4), delay: 100 })), 4);
assert.strictEqual(s.length, 4); assert.strictEqual(s.reduce((t, f) => t + f.delay, 0), 1000);
assert.throws(() => read(new Uint8Array([1, 2, 3, 4, 5, 6, 7])), /Not a GIF/);
assert.throws(() => read(fs.readFileSync(fixtures + '/plain.gif').subarray(0, 100)), /Unexpected end|GIF/);
console.log('gif reader ok');

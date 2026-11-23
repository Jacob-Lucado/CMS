/*
 * Reads every frame of a GIF in the browser, so animated pictures can be imported into the badge drawer.
 * GifReader.read(bytes) returns { width, height, frames: [{ rgba: Uint8ClampedArray, delay: ms }] }.
 * Each frame is the full picture as it looks on screen at that moment.
 */
(function (root, factory) {
    if (typeof module === 'object' && module.exports) {
        module.exports = factory();
    } else {
        root.GifReader = factory();
    }
})(typeof self !== 'undefined' ? self : this, function () {
    'use strict';

    var MAX_SIDE = 2048;
    var MAX_FRAMES = 300;

    function fail(message) {
        throw new Error(message);
    }

    function skipSubBlocks(bytes, position) {
        while (true) {
            if (position >= bytes.length) {
                fail('Unexpected end of GIF');
            }

            var size = bytes[position++];

            if (size === 0) {
                return position;
            }

            position += size;
        }
    }

    function readSubBlocks(bytes, position) {
        var chunks = [];
        var total = 0;

        while (true) {
            if (position >= bytes.length) {
                fail('Unexpected end of GIF');
            }

            var size = bytes[position++];

            if (size === 0) {
                break;
            }

            if (position + size > bytes.length) {
                fail('Unexpected end of GIF');
            }

            chunks.push(bytes.subarray(position, position + size));
            total += size;
            position += size;
        }

        var data = new Uint8Array(total);
        var offset = 0;

        chunks.forEach(function (chunk) {
            data.set(chunk, offset);
            offset += chunk.length;
        });

        return { data: data, position: position };
    }

    function lzw(minCodeSize, data, pixelCount) {
        if (minCodeSize < 2 || minCodeSize > 8) {
            fail('Bad GIF code size');
        }

        var clear = 1 << minCodeSize;
        var end = clear + 1;
        var prefix = new Int16Array(4096);
        var suffix = new Uint8Array(4096);
        var stack = new Uint8Array(4097);
        var out = new Uint8Array(pixelCount);
        var written = 0;
        var codeSize = minCodeSize + 1;
        var next = end + 1;
        var previous = -1;
        var first = 0;
        var bits = 0;
        var bitCount = 0;
        var index = 0;

        for (var i = 0; i < clear; i++) {
            suffix[i] = i;
        }

        while (written < pixelCount) {
            while (bitCount < codeSize) {
                if (index >= data.length) {
                    return out;
                }

                bits |= data[index++] << bitCount;
                bitCount += 8;
            }

            var code = bits & ((1 << codeSize) - 1);
            bits >>= codeSize;
            bitCount -= codeSize;

            if (code === clear) {
                codeSize = minCodeSize + 1;
                next = end + 1;
                previous = -1;

                continue;
            }

            if (code === end) {
                break;
            }

            if (previous === -1) {
                if (code >= clear) {
                    fail('Bad GIF data');
                }

                out[written++] = suffix[code];
                previous = code;
                first = code;

                continue;
            }

            var current = code;
            var top = 0;

            if (code >= next) {
                if (code > next) {
                    fail('Bad GIF data');
                }

                stack[top++] = first;
                current = previous;
            }

            while (current >= clear) {
                stack[top++] = suffix[current];
                current = prefix[current];
            }

            first = suffix[current];
            stack[top++] = first;

            if (next < 4096) {
                prefix[next] = previous;
                suffix[next] = first;
                next++;

                if (next === (1 << codeSize) && codeSize < 12) {
                    codeSize++;
                }
            }

            previous = code;

            while (top > 0 && written < pixelCount) {
                out[written++] = stack[--top];
            }
        }

        return out;
    }

    function read(bytes) {
        if (!(bytes instanceof Uint8Array)) {
            bytes = new Uint8Array(bytes);
        }

        var header = String.fromCharCode(bytes[0], bytes[1], bytes[2], bytes[3], bytes[4], bytes[5]);

        if (header !== 'GIF87a' && header !== 'GIF89a') {
            fail('Not a GIF');
        }

        var width = bytes[6] | (bytes[7] << 8);
        var height = bytes[8] | (bytes[9] << 8);

        if (width < 1 || height < 1 || width > MAX_SIDE || height > MAX_SIDE) {
            fail('GIF is too big');
        }

        var flags = bytes[10];
        var position = 13;
        var globalTable = null;

        if (flags & 0x80) {
            var globalSize = 3 * (1 << ((flags & 7) + 1));
            globalTable = bytes.subarray(position, position + globalSize);
            position += globalSize;
        }

        var canvas = new Uint8ClampedArray(width * height * 4);
        var frames = [];
        var gce = { delay: 100, disposal: 0, transparent: -1 };

        while (position < bytes.length) {
            var marker = bytes[position++];

            if (marker === 0x3b) {
                break;
            }

            if (marker === 0x21) {
                var label = bytes[position++];

                if (label === 0xf9 && bytes[position] === 4) {
                    var packed = bytes[position + 1];
                    var delay = (bytes[position + 2] | (bytes[position + 3] << 8)) * 10;
                    gce = {
                        delay: delay <= 10 ? 100 : delay,
                        disposal: (packed >> 2) & 7,
                        transparent: packed & 1 ? bytes[position + 4] : -1
                    };
                }

                position = skipSubBlocks(bytes, position);

                continue;
            }

            if (marker !== 0x2c) {
                fail('Bad GIF block');
            }

            var left = bytes[position] | (bytes[position + 1] << 8);
            var top = bytes[position + 2] | (bytes[position + 3] << 8);
            var frameWidth = bytes[position + 4] | (bytes[position + 5] << 8);
            var frameHeight = bytes[position + 6] | (bytes[position + 7] << 8);
            var frameFlags = bytes[position + 8];
            position += 9;

            var table = globalTable;

            if (frameFlags & 0x80) {
                var localSize = 3 * (1 << ((frameFlags & 7) + 1));
                table = bytes.subarray(position, position + localSize);
                position += localSize;
            }

            if (!table) {
                fail('GIF has no colors');
            }

            var minCodeSize = bytes[position++];
            var blocks = readSubBlocks(bytes, position);
            position = blocks.position;

            var indexes = lzw(minCodeSize, blocks.data, frameWidth * frameHeight);

            if (frameFlags & 0x40) {
                indexes = deinterlace(indexes, frameWidth, frameHeight);
            }

            var saved = gce.disposal === 3 ? new Uint8ClampedArray(canvas) : null;

            for (var y = 0; y < frameHeight; y++) {
                var targetY = top + y;

                if (targetY >= height) {
                    break;
                }

                for (var x = 0; x < frameWidth; x++) {
                    var targetX = left + x;

                    if (targetX >= width) {
                        break;
                    }

                    var colorIndex = indexes[y * frameWidth + x];

                    if (colorIndex === gce.transparent || colorIndex * 3 + 2 >= table.length) {
                        continue;
                    }

                    var target = (targetY * width + targetX) * 4;
                    canvas[target] = table[colorIndex * 3];
                    canvas[target + 1] = table[colorIndex * 3 + 1];
                    canvas[target + 2] = table[colorIndex * 3 + 2];
                    canvas[target + 3] = 255;
                }
            }

            frames.push({ rgba: new Uint8ClampedArray(canvas), delay: gce.delay });

            if (frames.length > MAX_FRAMES) {
                fail('GIF has too many frames');
            }

            if (gce.disposal === 2) {
                for (var cy = top; cy < Math.min(height, top + frameHeight); cy++) {
                    for (var cx = left; cx < Math.min(width, left + frameWidth); cx++) {
                        canvas.fill(0, (cy * width + cx) * 4, (cy * width + cx) * 4 + 4);
                    }
                }
            } else if (gce.disposal === 3 && saved) {
                canvas.set(saved);
            }

            gce = { delay: 100, disposal: 0, transparent: -1 };
        }

        if (frames.length === 0) {
            fail('GIF has no pictures');
        }

        return { width: width, height: height, frames: frames };
    }

    function deinterlace(indexes, width, height) {
        var out = new Uint8Array(indexes.length);
        var passes = [[0, 8], [4, 8], [2, 4], [1, 2]];
        var source = 0;

        passes.forEach(function (pass) {
            for (var y = pass[0]; y < height; y += pass[1]) {
                out.set(indexes.subarray(source * width, source * width + width), y * width);
                source++;
            }
        });

        return out;
    }

    /** Picks at most `limit` frames, spread evenly. A skipped frame's time is added to the one before it. */
    function sample(frames, limit) {
        if (frames.length <= limit) {
            return frames.map(function (frame) {
                return { rgba: frame.rgba, delay: frame.delay };
            });
        }

        var picked = [];

        for (var i = 0; i < limit; i++) {
            var start = Math.floor((i * frames.length) / limit);
            var stop = Math.floor(((i + 1) * frames.length) / limit);
            var delay = 0;

            for (var j = start; j < stop; j++) {
                delay += frames[j].delay;
            }

            picked.push({ rgba: frames[start].rgba, delay: delay });
        }

        return picked;
    }

    return { read: read, sample: sample, MAX_FRAMES: MAX_FRAMES };
});

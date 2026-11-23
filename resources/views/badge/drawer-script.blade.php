    <script>
        function badgeDrawer({ cost, currencyType }) {
            return {
                cost: cost,
                currencyType: currencyType,
                color: '#000000',
                recentColors: [],
                eraseMode: false,
                copyMode: false,
                showGrid: true,
                isDrawing: false,
                cellSideCount: 40,
                colorHistory: {},
                badgeName: '',
                badgeDescription: '',
                lastBuyTime: null,
                canBuy: true,
                buttonText: `{{ __('Buy Badge') }} (${cost} ${currencyType.charAt(0).toUpperCase() + currencyType.slice(1)})`,
                drawingContext: null,
                previewContext: null,
                guide: null,
                canvas: null,
                previewCanvas: null,
                frames: [],
                current: 0,
                playing: false,
                playTimer: null,
                maxFrames: {{ \App\Rules\ValidGifBadge::MAX_FRAMES }},
                minDelay: {{ \App\Rules\ValidGifBadge::MIN_DELAY * 10 }},
                maxDelay: 2000,
                maxBytes: {{ \App\Rules\ValidGifBadge::MAX_ANIMATED_BYTES }},
                colors: [
                    '#000000', '#FFFFFF', '#808080', '#C0C0C0', '#FF0000', '#800000',
                    '#FFFF00', '#808000', '#00FF00', '#008000', '#00FFFF', '#008080',
                    '#0000FF', '#000080', '#FF00FF', '#800080', '#FF4500', '#FFA500',
                    '#FFD700', '#F0E68C', '#90EE90', '#98FB98', '#AFEEEE', '#ADD8E6',
                    '#87CEFA', '#6495ED', '#DDA0DD', '#EE82EE', '#A52A2A', '#D2691E',
                    '#CD853F', '#F4A460', '#FFC0CB', '#9370DB', '#228B22', '#20B2AA'
                ],

                get hasContent() {
                    return Object.keys(this.colorHistory).length > 0
                        || this.frames.some((frame, index) => index !== this.current && Object.keys(frame.history).length > 0);
                },

                get isValid() {
                    return this.badgeName.trim().length > 0 && this.badgeName.trim().length <= 24 && this.badgeDescription.trim().length > 0 && this.badgeDescription.trim().length <= 255 && this.hasContent && !this.badgeName.match(/https?:\/\/|www\./i) && !this.badgeDescription.match(/https?:\/\/|www\./i) && this.canBuy;
                },

                init() {
                    this.canvas = this.$refs.canvas;
                    this.previewCanvas = this.$refs.previewCanvas;
                    this.guide = this.$refs.guide;
                    this.drawingContext = this.canvas.getContext('2d');
                    this.previewContext = this.previewCanvas.getContext('2d');
                    this.frames = [this.blankFrame()];

                    // Get actual rendered size of the canvas
                    const canvasWidth = this.canvas.clientWidth;
                    const cellSize = canvasWidth / this.cellSideCount;

                    // Setup the guide with dynamic grid lines using gradients (no borders, no child divs)
                    this.guide.style.width = `${canvasWidth}px`;
                    this.guide.style.height = `${canvasWidth}px`;

                    // Clear any existing children (no longer needed)
                    this.guide.innerHTML = '';

                    // Determine border color based on dark mode
                    const isDark = document.documentElement.classList.contains('dark');
                    const borderColor = isDark ? '#fff' : '#4b5563'; // gray-700

                    // Set grid lines as background gradients
                    this.guide.style.backgroundImage = `
                        repeating-linear-gradient(to bottom, ${borderColor} 0px 1px, transparent 1px ${cellSize}px),
                        repeating-linear-gradient(to right, ${borderColor} 0px 1px, transparent 1px ${cellSize}px)
                    `;

                    // Watch for grid toggle (use block instead of grid)
                    this.$watch('showGrid', (value) => {
                        this.guide.style.display = value ? 'block' : 'none';
                    });

                    // Make checkerboard dynamic and aligned to cellSize
                    const checkerboardEl = this.$el.querySelector('.checkerboard');
                    let bgColor, checkColor;
                    if (isDark) {
                        bgColor = '#1a1a1a';
                        checkColor = '#2a2a2a';
                    } else {
                        bgColor = '#fff';
                        checkColor = '#eee';
                    }
                    const checkUnit = cellSize / 2; // Half cell for finer checks (2x2 per cell)
                    checkerboardEl.style.backgroundColor = bgColor;
                    checkerboardEl.style.backgroundImage = `
                        linear-gradient(45deg, ${checkColor} 25%, transparent 25%),
                        linear-gradient(-45deg, ${checkColor} 25%, transparent 25%),
                        linear-gradient(45deg, transparent 75%, ${checkColor} 75%),
                        linear-gradient(-45deg, transparent 75%, ${checkColor} 75%)
                    `;
                    checkerboardEl.style.backgroundSize = `${checkUnit * 2}px ${checkUnit * 2}px`;
                    checkerboardEl.style.backgroundPosition = `0 0, 0 ${checkUnit}px, ${checkUnit}px -${checkUnit}px, -${checkUnit}px 0px`;

                    // Watch for erase/copy toggle
                    this.$watch('eraseMode', (value) => {
                        if (value) this.copyMode = false;
                    });
                    this.$watch('copyMode', (value) => {
                        if (value) this.eraseMode = false;
                    });

                    // Mouse events
                    this.canvas.addEventListener('mousedown', this.handleMousedown.bind(this));
                    this.canvas.addEventListener('mousemove', this.handleMousemove.bind(this));
                    this.canvas.addEventListener('mouseup', () => { this.isDrawing = false; });
                    this.canvas.addEventListener('mouseleave', () => { this.isDrawing = false; });

                    // Touch events
                    this.canvas.addEventListener('touchstart', this.handleTouchstart.bind(this));
                    this.canvas.addEventListener('touchmove', this.handleTouchmove.bind(this));
                    this.canvas.addEventListener('touchend', () => { this.isDrawing = false; });
                    this.canvas.addEventListener('touchcancel', () => { this.isDrawing = false; });

                    // Initial preview
                    this.updatePreview();
                },

                toggleCopyMode() {
                    this.copyMode = !this.copyMode;
                    if (this.copyMode) {
                        this.eraseMode = false;
                    }
                },

                toggleEraseMode() {
                    this.eraseMode = !this.eraseMode;
                    if (this.eraseMode) {
                        this.copyMode = false;
                    }
                },

                handleMousedown(e) {
                    if (e.button !== 0) return;

                    const canvasBoundingRect = this.canvas.getBoundingClientRect();
                    const scaleX = this.canvas.width / canvasBoundingRect.width;
                    const scaleY = this.canvas.height / canvasBoundingRect.height;
                    const x = (e.clientX - canvasBoundingRect.left) * scaleX;
                    const y = (e.clientY - canvasBoundingRect.top) * scaleY;
                    const cellX = Math.floor(x);
                    const cellY = Math.floor(y);
                    const currentColor = this.colorHistory[`${cellX}_${cellY}`];

                    if (this.copyMode) {
                        if (currentColor) {
                            this.color = currentColor;
                            this.copyMode = false;
                        }
                    } else if (e.ctrlKey) {
                        if (currentColor) {
                            this.color = currentColor;
                        }
                    } else {
                        this.paintCell(cellX, cellY);
                        this.isDrawing = true;
                    }
                },

                handleMousemove(e) {
                    if (!this.isDrawing) return;

                    const canvasBoundingRect = this.canvas.getBoundingClientRect();
                    const scaleX = this.canvas.width / canvasBoundingRect.width;
                    const scaleY = this.canvas.height / canvasBoundingRect.height;
                    const x = (e.clientX - canvasBoundingRect.left) * scaleX;
                    const y = (e.clientY - canvasBoundingRect.top) * scaleY;
                    const cellX = Math.floor(x);
                    const cellY = Math.floor(y);

                    this.paintCell(cellX, cellY);
                },

                handleTouchstart(e) {
                    e.preventDefault();
                    const touch = e.touches[0];
                    const canvasBoundingRect = this.canvas.getBoundingClientRect();
                    const scaleX = this.canvas.width / canvasBoundingRect.width;
                    const scaleY = this.canvas.height / canvasBoundingRect.height;
                    const x = (touch.clientX - canvasBoundingRect.left) * scaleX;
                    const y = (touch.clientY - canvasBoundingRect.top) * scaleY;
                    const cellX = Math.floor(x);
                    const cellY = Math.floor(y);
                    const currentColor = this.colorHistory[`${cellX}_${cellY}`];

                    if (this.copyMode) {
                        if (currentColor) {
                            this.color = currentColor;
                            this.copyMode = false;
                        }
                    } else {
                        this.paintCell(cellX, cellY);
                        this.isDrawing = true;
                    }
                },

                handleTouchmove(e) {
                    e.preventDefault();
                    if (!this.isDrawing) return;
                    const touch = e.touches[0];
                    const canvasBoundingRect = this.canvas.getBoundingClientRect();
                    const scaleX = this.canvas.width / canvasBoundingRect.width;
                    const scaleY = this.canvas.height / canvasBoundingRect.height;
                    const x = (touch.clientX - canvasBoundingRect.left) * scaleX;
                    const y = (touch.clientY - canvasBoundingRect.top) * scaleY;
                    const cellX = Math.floor(x);
                    const cellY = Math.floor(y);

                    this.paintCell(cellX, cellY);
                },

                importImage(e) {
                    const file = e.target.files[0];
                    if (!file) return;

                    if (!['image/png', 'image/gif'].includes(file.type)) {
                        alert(translations.invalid_file_type);
                        e.target.value = '';
                        return;
                    }

                    if (!confirm(@js(__('Import image and overwrite the canvas?')))) {
                        e.target.value = '';
                        return;
                    }

                    const invalid = () => {
                        alert(@js(__('Invalid image format. Please upload a valid PNG or GIF file.')));
                        e.target.value = '';
                    };

                    if (file.type === 'image/gif') {
                        file.arrayBuffer().then((buffer) => {
                            if (buffer.byteLength > 5 * 1024 * 1024) {
                                alert(@js(__('That GIF is too big. Pick one under 5 MB.')));
                                e.target.value = '';
                                return;
                            }

                            let gif;
                            try {
                                gif = GifReader.read(new Uint8Array(buffer));
                            } catch (error) {
                                invalid();
                                return;
                            }

                            const picked = GifReader.sample(gif.frames, this.maxFrames);
                            const source = document.createElement('canvas');
                            source.width = gif.width;
                            source.height = gif.height;
                            const sourceContext = source.getContext('2d');

                            this.useFrames(picked.map((frame) => {
                                sourceContext.putImageData(new ImageData(frame.rgba, gif.width, gif.height), 0, 0);
                                return { image: this.fitToBoard(source, gif.width, gif.height), delay: this.clampDelay(frame.delay) };
                            }));

                            if (gif.frames.length > picked.length) {
                                alert(@js(__('That GIF has more frames than a badge can hold, so some were skipped to keep the motion.')));
                            }

                            e.target.value = '';
                        }).catch(invalid);

                        return;
                    }

                    const reader = new FileReader();
                    reader.onload = (event) => {
                        const img = new Image();
                        img.onerror = invalid;
                        img.onload = () => {
                            this.useFrames([{ image: this.fitToBoard(img, img.naturalWidth, img.naturalHeight), delay: 100 }]);
                            e.target.value = '';
                        };
                        img.src = event.target.result;
                    };
                    reader.readAsDataURL(file);
                },

                /** Scales a picture onto the 40x40 board and drops half-transparent pixels. */
                fitToBoard(source, width, height) {
                    const size = this.cellSideCount;
                    const board = document.createElement('canvas');
                    board.width = size;
                    board.height = size;
                    const context = board.getContext('2d');
                    // Small pixel art stays sharp, big pictures are smoothed down.
                    context.imageSmoothingEnabled = width > size || height > size;
                    context.drawImage(source, 0, 0, size, size);

                    const imageData = context.getImageData(0, 0, size, size);
                    for (let i = 0; i < imageData.data.length; i += 4) {
                        imageData.data[i + 3] = imageData.data[i + 3] < 128 ? 0 : 255;
                    }

                    return imageData;
                },

                /** Replaces everything on the board with these pictures, one per frame. */
                useFrames(pictures) {
                    this.stopPlaying();
                    this.recentColors = [];

                    this.frames = pictures.map((picture) => {
                        const frame = this.blankFrame();
                        const history = {};
                        frame.data = picture.image;
                        frame.delay = picture.delay;

                        for (let y = 0; y < this.cellSideCount; y++) {
                            for (let x = 0; x < this.cellSideCount; x++) {
                                const index = (y * this.cellSideCount + x) * 4;

                                if (picture.image.data[index + 3] > 0) {
                                    const hex = this.rgbToHex(picture.image.data[index], picture.image.data[index + 1], picture.image.data[index + 2]);
                                    history[`${x}_${y}`] = hex;

                                    if (!this.recentColors.includes(hex) && this.recentColors.length < 12) {
                                        this.recentColors.push(hex);
                                    }
                                }
                            }
                        }

                        frame.history = history;

                        return frame;
                    });

                    this.current = -1;
                    this.loadFrame(0);
                },

                rgbToHex(r, g, b) {
                    r = Math.round(r);
                    g = Math.round(g);
                    b = Math.round(b);
                    return '#' + ((1 << 24) | (r << 16) | (g << 8) | b).toString(16).slice(1).toUpperCase();
                },

                paintCell(cellX, cellY) {
                    if (this.playing) this.stopPlaying();

                    if (this.eraseMode) {
                        this.drawingContext.clearRect(cellX, cellY, 1, 1);
                        delete this.colorHistory[`${cellX}_${cellY}`];
                    } else {
                        this.drawingContext.fillStyle = this.color;
                        this.drawingContext.fillRect(cellX, cellY, 1, 1);
                        this.colorHistory[`${cellX}_${cellY}`] = this.color;
                        // Add to recentColors only when painting
                        if (!this.recentColors.includes(this.color)) {
                            this.recentColors.unshift(this.color);
                            if (this.recentColors.length > 12) {
                                this.recentColors = this.recentColors.slice(0, 12);
                            }
                        }
                    }
                    this.updatePreview();
                },

                clearBoard() {
                    if (!confirm(@js(__('Clear the entire board?')))) return;
                    this.stopPlaying();
                    this.drawingContext.clearRect(0, 0, this.canvas.width, this.canvas.height);
                    this.colorHistory = {};
                    this.recentColors = []; // Clear recent colors as well
                    this.frames = [this.blankFrame()];
                    this.current = 0;
                    this.updatePreview();
                },

                updatePreview() {
                    this.previewContext.clearRect(0, 0, this.previewCanvas.width, this.previewCanvas.height);
                    this.previewContext.drawImage(this.canvas, 0, 0);

                    if (this.frames[this.current]) {
                        this.frames[this.current].thumb = this.canvas.toDataURL();
                    }
                },

                blankFrame() {
                    return { data: null, history: {}, delay: 100, thumb: '' };
                },

                clampDelay(value) {
                    const number = Number(value);
                    return Math.min(this.maxDelay, Math.max(this.minDelay, Number.isFinite(number) ? Math.round(number / 10) * 10 : 100));
                },

                saveFrame() {
                    const frame = this.frames[this.current];
                    frame.data = this.drawingContext.getImageData(0, 0, this.canvas.width, this.canvas.height);
                    frame.history = this.colorHistory;
                },

                loadFrame(index) {
                    this.stopPlaying();
                    this.current = index;
                    const frame = this.frames[index];
                    this.drawingContext.clearRect(0, 0, this.canvas.width, this.canvas.height);

                    if (frame.data) {
                        this.drawingContext.putImageData(frame.data, 0, 0);
                    }

                    this.colorHistory = frame.history;
                    this.updatePreview();
                },

                selectFrame(index) {
                    if (index === this.current) return;
                    this.saveFrame();
                    this.loadFrame(index);
                },

                addFrame(copy) {
                    if (this.frames.length >= this.maxFrames) return;

                    this.saveFrame();
                    const source = this.frames[this.current];
                    const frame = this.blankFrame();

                    if (copy) {
                        frame.data = new ImageData(new Uint8ClampedArray(source.data.data), this.canvas.width, this.canvas.height);
                        frame.history = { ...source.history };
                        frame.delay = source.delay;
                        frame.thumb = source.thumb;
                    } else {
                        frame.delay = source.delay;
                    }

                    this.frames.splice(this.current + 1, 0, frame);
                    this.loadFrame(this.current + 1);
                },

                removeFrame() {
                    if (this.frames.length <= 1) return;
                    if (!confirm(@js(__('Delete this frame?')))) return;

                    this.frames.splice(this.current, 1);
                    this.loadFrame(Math.min(this.current, this.frames.length - 1));
                },

                moveFrame(direction) {
                    const target = this.current + direction;
                    if (target < 0 || target >= this.frames.length) return;

                    this.saveFrame();
                    const [frame] = this.frames.splice(this.current, 1);
                    this.frames.splice(target, 0, frame);
                    this.current = target;
                },

                togglePlay() {
                    if (this.playing) {
                        this.stopPlaying();
                        return;
                    }

                    if (this.frames.length < 2) return;

                    this.saveFrame();
                    this.playing = true;
                    const step = (index) => {
                        if (!this.playing) return;

                        const frame = this.frames[index];
                        this.previewContext.clearRect(0, 0, this.previewCanvas.width, this.previewCanvas.height);

                        if (frame.data) {
                            this.previewContext.putImageData(frame.data, 0, 0);
                        }

                        this.playTimer = setTimeout(() => step((index + 1) % this.frames.length), this.clampDelay(frame.delay));
                    };
                    step(0);
                },

                stopPlaying() {
                    clearTimeout(this.playTimer);
                    this.playTimer = null;

                    if (this.playing) {
                        this.playing = false;
                        this.updatePreview();
                    }
                },

                generateGifBlob() {
                    return new Promise((resolve) => {
                        this.stopPlaying();
                        this.saveFrame();

                        // Find a colour no frame uses, to stand in for transparency.
                        const usedColors = new Set();
                        this.frames.forEach((frame) => {
                            if (!frame.data) return;

                            for (let i = 0; i < frame.data.data.length; i += 4) {
                                if (frame.data.data[i + 3] > 0) {
                                    usedColors.add((frame.data.data[i] << 16) | (frame.data.data[i + 1] << 8) | frame.data.data[i + 2]);
                                }
                            }
                        });

                        let transColor = 0xFF00FF;
                        while (usedColors.has(transColor)) {
                            transColor = (transColor + 1) % 0x1000000;
                        }
                        const transHex = '#' + transColor.toString(16).padStart(6, '0').toUpperCase();

                        const gif = new GIF({
                            workers: 2,
                            quality: 10,
                            workerScript: '{{ asset('assets/js/gif/gif.worker.js') }}', // Local worker script
                            width: this.canvas.width,
                            height: this.canvas.height,
                            transparent: transColor,
                            repeat: 0
                        });

                        this.frames.forEach((frame) => {
                            const picture = document.createElement('canvas');
                            picture.width = this.canvas.width;
                            picture.height = this.canvas.height;

                            if (frame.data) {
                                picture.getContext('2d').putImageData(frame.data, 0, 0);
                            }

                            const flat = document.createElement('canvas');
                            flat.width = picture.width;
                            flat.height = picture.height;
                            const flatContext = flat.getContext('2d');
                            flatContext.fillStyle = transHex;
                            flatContext.fillRect(0, 0, flat.width, flat.height);
                            flatContext.drawImage(picture, 0, 0);

                            // With a transparent colour the encoder clears each frame before the next one.
                            gif.addFrame(flat, { delay: this.clampDelay(frame.delay), copy: true });
                        });

                        gif.on('finished', (blob) => {
                            resolve(blob);
                        });

                        gif.render();
                    });
                },

                async generateCanvas(action) {
                    const blob = await this.generateGifBlob();

                    if (action === 'download') {
                        const url = URL.createObjectURL(blob);
                        const a = document.createElement('a');
                        a.href = url;
                        a.download = 'badge.gif';
                        document.body.appendChild(a);
                        a.click();
                        document.body.removeChild(a);
                        URL.revokeObjectURL(url);
                    }
                },

                async buyBadge() {
                    if (!this.hasContent || !this.badgeName.trim() || !this.badgeDescription.trim()) {
                        alert(translations.missing_fields);
                        return;
                    }

                    if (this.badgeName.match(/https?:\/\/|www\./i) || this.badgeDescription.match(/https?:\/\/|www\./i)) {
                        alert(translations.invalid_content);
                        return;
                    }

                    if (!this.canBuy || !confirm(translations.buy_confirmation)) return;

                    this.lastBuyTime = Date.now();
                    this.canBuy = false;
                    this.buttonText = `Cooldown (${Math.ceil((30000 - (Date.now() - this.lastBuyTime)) / 1000)} sec)`;

                    const interval = setInterval(() => {
                        const remainingTime = Math.ceil((30000 - (Date.now() - this.lastBuyTime)) / 1000);
                        if (remainingTime <= 0) {
                            clearInterval(interval);
                            this.canBuy = true;
                            this.buttonText = `{{ __('Buy Badge') }} (${this.cost} ${this.currencyType.charAt(0).toUpperCase() + this.currencyType.slice(1)})`;
                        } else {
                            this.buttonText = `Cooldown (${remainingTime} sec)`;
                        }
                    }, 1000);

                    const blob = await this.generateGifBlob();

                    if (blob.size > this.maxBytes) {
                        clearInterval(interval);
                        this.canBuy = true;
                        this.buttonText = `{{ __('Buy Badge') }} (${this.cost} ${this.currencyType.charAt(0).toUpperCase() + this.currencyType.slice(1)})`;
                        alert(@js(__('This animation is too big. Remove some frames or simplify the pictures.')));
                        return;
                    }

                    const reader = new FileReader();
                    reader.onloadend = () => {
                        const base64data = reader.result;

                        fetch('{{ route('badge.buy') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({ badge_data: base64data, badge_name: this.badgeName, badge_description: this.badgeDescription })
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                alert(translations.purchase_success);
                            } else {
                                alert(data.message || translations.purchase_error_insufficient);
                                clearInterval(interval); // Clear interval if buy fails
                                this.canBuy = true;
                                this.buttonText = `{{ __('Buy Badge') }} (${this.cost} ${this.currencyType.charAt(0).toUpperCase() + this.currencyType.slice(1)})`;
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            alert(translations.purchase_error_general);
                            clearInterval(interval); // Clear interval if buy fails
                            this.canBuy = true;
                            this.buttonText = `{{ __('Buy Badge') }} (${this.cost} ${this.currencyType.charAt(0).toUpperCase() + this.currencyType.slice(1)})`;
                        });
                    };
                    reader.readAsDataURL(blob);

                    // Ensure cooldown ends even if fetch fails or takes long
                    setTimeout(() => {
                        if (!this.canBuy) {
                            clearInterval(interval);
                            this.canBuy = true;
                            this.buttonText = `{{ __('Buy Badge') }} (${this.cost} ${this.currencyType.charAt(0).toUpperCase() + this.currencyType.slice(1)})`;
                        }
                    }, 30000);
                }
            }
        }
    </script>

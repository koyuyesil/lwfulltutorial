// Dijital kabul formu: fotoğraf küçültme + yükleme, imza alanı, desen kilidi.

/** Görseli en uzun kenarı max px olacak şekilde JPEG'e küçültür. */
export async function compressImage(file, max = 1600, quality = 0.82) {
    if (!file.type.startsWith('image/')) return file;
    const bitmap = await createImageBitmap(file).catch(() => null);
    if (!bitmap) return file;
    const scale = Math.min(1, max / Math.max(bitmap.width, bitmap.height));
    const canvas = document.createElement('canvas');
    canvas.width = Math.round(bitmap.width * scale);
    canvas.height = Math.round(bitmap.height * scale);
    canvas.getContext('2d').drawImage(bitmap, 0, 0, canvas.width, canvas.height);
    const blob = await new Promise((r) => canvas.toBlob(r, 'image/jpeg', quality));
    return blob ? new File([blob], file.name.replace(/\.\w+$/, '') + '.jpg', { type: 'image/jpeg' }) : file;
}

/** Desen kilidi: 3×3 noktada 1–9 sırası → "1-5-9" */
export function patternToPath(seq, size = 120, pad = 20) {
    const step = (size - pad * 2) / 2;
    const pos = (n) => [pad + ((n - 1) % 3) * step, pad + Math.floor((n - 1) / 3) * step];
    return seq.map((n, i) => { const [x, y] = pos(n); return `${i ? 'L' : 'M'}${x} ${y}`; }).join(' ');
}

export function registerIntake(Alpine) {
    Alpine.data('photoUploader', () => ({
        busy: false,
        progress: 0,
        over: false,
        async pick(files) {
            const list = [...(files || [])].filter((f) => f.type.startsWith('image/'));
            if (!list.length) return;
            this.busy = true;
            this.progress = 0;
            const compressed = await Promise.all(list.map((f) => compressImage(f)));
            this.$wire.uploadMultiple('uploads', compressed,
                () => { this.busy = false; },
                () => { this.busy = false; window.dispatchEvent(new CustomEvent('toast', { detail: { message: 'Fotoğraf yüklenemedi.', type: 'error' } })); },
                (e) => { this.progress = e.detail.progress; });
        },
    }));

    Alpine.data('signaturePad', () => ({
        drawing: false,
        empty: true,
        init() {
            const c = this.$refs.canvas;
            const fit = () => {
                const ratio = window.devicePixelRatio || 1;
                const { width, height } = c.getBoundingClientRect();
                if (!width) return;
                c.width = width * ratio;
                c.height = height * ratio;
                const ctx = c.getContext('2d');
                ctx.scale(ratio, ratio);
                ctx.lineWidth = 2.4;
                ctx.lineCap = 'round';
                ctx.lineJoin = 'round';
                ctx.strokeStyle = '#0f172a';
                this.empty = true;
            };
            fit();
            this._ro = new ResizeObserver(() => { if (this.empty) fit(); });
            this._ro.observe(c);
        },
        destroy() { this._ro?.disconnect(); },
        point(e) {
            const r = this.$refs.canvas.getBoundingClientRect();
            return [e.clientX - r.left, e.clientY - r.top];
        },
        start(e) {
            e.preventDefault();
            this.drawing = true;
            this.$refs.canvas.setPointerCapture?.(e.pointerId);
            const ctx = this.$refs.canvas.getContext('2d');
            const [x, y] = this.point(e);
            ctx.beginPath();
            ctx.moveTo(x, y);
            ctx.lineTo(x + 0.1, y + 0.1);
            ctx.stroke();
        },
        move(e) {
            if (!this.drawing) return;
            const ctx = this.$refs.canvas.getContext('2d');
            const [x, y] = this.point(e);
            ctx.lineTo(x, y);
            ctx.stroke();
            this.empty = false;
        },
        end() { this.drawing = false; },
        clear() {
            const c = this.$refs.canvas;
            c.getContext('2d').clearRect(0, 0, c.width, c.height);
            this.empty = true;
        },
        /** Kırpılmış, şeffaf zeminli PNG */
        dataUrl() { return this.$refs.canvas.toDataURL('image/png'); },
    }));

    Alpine.data('patternLock', (initial = '') => ({
        seq: String(initial || '').split('-').map(Number).filter((n) => n >= 1 && n <= 9),
        drawing: false,
        get path() { return patternToPath(this.seq); },
        get value() { return this.seq.join('-'); },
        hit(e) {
            const r = this.$refs.grid.getBoundingClientRect();
            const x = (e.clientX - r.left) / r.width * 120;
            const y = (e.clientY - r.top) / r.height * 120;
            for (let n = 1; n <= 9; n++) {
                const cx = 20 + ((n - 1) % 3) * 40;
                const cy = 20 + Math.floor((n - 1) / 3) * 40;
                if (Math.hypot(x - cx, y - cy) < 14) return n;
            }
            return null;
        },
        down(e) { this.seq = []; this.drawing = true; this.$refs.grid.setPointerCapture?.(e.pointerId); this.add(e); },
        add(e) {
            if (!this.drawing) return;
            const n = this.hit(e);
            if (n && !this.seq.includes(n)) this.seq.push(n);
        },
        up() { this.drawing = false; this.$dispatch('pattern-change', this.value); },
        reset() { this.seq = []; this.$dispatch('pattern-change', ''); },
    }));
}

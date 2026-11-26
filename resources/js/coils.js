// Bobin hesaplayıcıları: endüktans formülleri ve 3D (izdüşümlü SVG) sarım çizimleri. Tamamı tarayıcıda, anlık.
import { parseSI } from './calculators.js';

const IN = 25.4; // mm / inç (Wheeler formülleri inç ile verilir)
const RHO_CU = 0.0172; // bakır özdirenci, Ω·mm²/m (20 °C)
const ok = (...v) => v.every((x) => Number.isFinite(x) && x > 0);

/* ------------------------------------------------------------------ Formüller (SI: H, m; girişler mm) */

/** Tek katmanlı hava çekirdekli solenoid (Wheeler): L[µH] = d²·N² / (18d + 40l), d ve l inç. d: bobin çapı (tel merkezinden) */
export function solenoidL(dMm, lenMm, n) {
    if (!ok(dMm, lenMm, n)) return NaN;
    const d = dMm / IN;
    const l = lenMm / IN;
    return (d * d * n * n) / (18 * d + 40 * l) * 1e-6;
}

/** Hedef endüktans için sarım sayısı (uzunluk sabit) */
export function solenoidTurns(dMm, lenMm, L) {
    if (!ok(dMm, lenMm, L)) return NaN;
    const d = dMm / IN;
    const l = lenMm / IN;
    return Math.sqrt(L * 1e6 * (18 * d + 40 * l)) / d;
}

/** Çok katmanlı hava bobini (Wheeler): L[µH] = 0.8·a²·N² / (6a + 9b + 10c); a ortalama yarıçap, b uzunluk, c sarım derinliği (inç) */
export function multilayerL(diMm, doMm, lenMm, n) {
    if (!ok(diMm, doMm, lenMm, n) || doMm <= diMm) return NaN;
    const a = (diMm + doMm) / 4 / IN;
    const b = lenMm / IN;
    const c = (doMm - diMm) / 2 / IN;
    return (0.8 * a * a * n * n) / (6 * a + 9 * b + 10 * c) * 1e-6;
}

export function multilayerTurns(diMm, doMm, lenMm, L) {
    if (!ok(diMm, doMm, lenMm, L) || doMm <= diMm) return NaN;
    const a = (diMm + doMm) / 4 / IN;
    const b = lenMm / IN;
    const c = (doMm - diMm) / 2 / IN;
    return Math.sqrt(L * 1e6 * (6 * a + 9 * b + 10 * c) / (0.8 * a * a));
}

/** Toroid / ferrit çekirdek: L = AL · N² (AL: nH/sarım²) */
export function toroidL(alNh, n) {
    return ok(alNh, n) ? alNh * 1e-9 * n * n : NaN;
}

export function toroidTurns(alNh, L) {
    return ok(alNh, L) ? Math.sqrt(L / (alNh * 1e-9)) : NaN;
}

/** Düz spiral (kablosuz şarj / NFC bobini, Wheeler): L[µH] = r²·N² / (8r + 11w); r ortalama yarıçap, w sarım genişliği (inç) */
export function spiralL(diMm, doMm, n) {
    if (!ok(diMm, doMm, n) || doMm <= diMm) return NaN;
    const r = (diMm + doMm) / 4 / IN;
    const w = (doMm - diMm) / 2 / IN;
    return (r * r * n * n) / (8 * r + 11 * w) * 1e-6;
}

export function spiralTurns(diMm, doMm, L) {
    if (!ok(diMm, doMm, L) || doMm <= diMm) return NaN;
    const r = (diMm + doMm) / 4 / IN;
    const w = (doMm - diMm) / 2 / IN;
    return Math.sqrt(L * 1e6 * (8 * r + 11 * w)) / r;
}

/** LC rezonans frekansı (Hz) */
export function resonance(L, C) {
    return ok(L, C) ? 1 / (2 * Math.PI * Math.sqrt(L * C)) : NaN;
}

/** Bakır telin DC direnci (Ω): uzunluk m, tel çapı mm */
export function copperResistance(lengthM, wireMm) {
    if (!ok(lengthM, wireMm)) return NaN;
    return RHO_CU * lengthM / (Math.PI * (wireMm / 2) ** 2);
}

/** Endüktansın okunur birimi: 0.0000047 → { value: 4.7, unit: 'µH' } yerine si() kullanılır; burada yalnızca güvenli sayı */
export const finite = (v) => (Number.isFinite(v) ? v : NaN);

/* ------------------------------------------------------------------ 3D geometri (izdüşüm) */

/**
 * Sarım yolunu ön / arka parçalara ayırır (arka parçalar çekirdeğin arkasında, soluk çizilir).
 * points: [{ x, y, front }] → { front: 'M…L…', back: 'M…L…' }
 */
export function splitPath(points) {
    let front = '';
    let back = '';
    let prev = null;
    for (const p of points) {
        const seg = `${p.x.toFixed(1)} ${p.y.toFixed(1)}`;
        if (!prev || prev.front !== p.front) {
            // Kopukluk olmasın: yeni parça bir önceki noktadan başlar
            const start = prev ? `M${prev.x.toFixed(1)} ${prev.y.toFixed(1)} L${seg}` : `M${seg}`;
            if (p.front) front += start; else back += start;
        } else if (p.front) {
            front += ` L${seg}`;
        } else {
            back += ` L${seg}`;
        }
        prev = p;
    }
    return { front, back };
}

/**
 * Yatay eksenli sarmal (solenoid). Eksen etrafında phase kadar döner; yaw: izleyiciye doğru yan bakış açısı.
 * Döner: ön / arka yol, uç noktaları, elips yarıçapı.
 */
export function helix({ turns, r, len, phase = 0, cx = 200, cy = 120, yaw = 0.45, steps = 36, rTo = null }) {
    const n = Math.max(0.5, Math.min(turns, 80));
    const total = Math.max(24, Math.round(n * steps));
    const sinY = Math.sin(yaw);
    const cosY = Math.cos(yaw);
    const pts = [];
    for (let k = 0; k <= total; k++) {
        const t = k / total;
        const th = 2 * Math.PI * n * t + phase;
        const rad = rTo === null ? r : r + (rTo - r) * t;
        const x = -len / 2 + len * t;
        const y = rad * Math.cos(th);
        const z = rad * Math.sin(th);
        pts.push({ x: cx + x * cosY + z * sinY, y: cy + y, front: z >= 0 });
    }
    return { ...splitPath(pts), start: pts[0], end: pts[pts.length - 1], ex: r * sinY };
}

/** Archimedes spirali (düz bobin), merkez (0,0); rIn → rOut, n sarım. Tek yol (düz çizilir, 3D eğim CSS ile). */
export function spiral({ n, rIn, rOut, phase = 0, steps = 72 }) {
    const turns = Math.max(0.5, Math.min(n, 60));
    const total = Math.max(36, Math.round(turns * steps));
    let d = '';
    let end = null;
    let start = null;
    for (let k = 0; k <= total; k++) {
        const t = k / total;
        const th = 2 * Math.PI * turns * t + phase;
        const rad = rIn + (rOut - rIn) * t;
        const x = rad * Math.cos(th);
        const y = rad * Math.sin(th);
        d += `${k ? ' L' : 'M'}${x.toFixed(1)} ${y.toFixed(1)}`;
        if (!k) start = { x, y };
        end = { x, y };
    }
    return { d, start, end };
}

/**
 * Toroid: halka (büyük yarıçap R, tüp yarıçapı r) üzerinde eşit aralıklı sarımlar.
 * Görüş eğimi tilt (radyan, 0 = yandan, π/2 = üstten). Arka parçalar halkanın arkasında kalır.
 */
export function toroid({ turns, R, r, phase = 0, cx = 200, cy = 120, tilt = 1.0, steps = 28 }) {
    const n = Math.max(1, Math.min(Math.round(turns), 64));
    const cosT = Math.cos(tilt);
    const sinT = Math.sin(tilt);
    let front = '';
    let back = '';
    for (let i = 0; i < n; i++) {
        const a = phase + (2 * Math.PI * i) / n;
        const pts = [];
        for (let k = 0; k <= steps; k++) {
            const phi = (2 * Math.PI * k) / steps;
            const rho = R + r * 1.18 * Math.cos(phi); // tel, tüpün biraz dışından geçer
            const h = r * 1.18 * Math.sin(phi);
            const X = rho * Math.cos(a);
            const Y = rho * Math.sin(a);
            pts.push({ x: cx + X, y: cy + Y * cosT - h * sinT, front: Y * sinT + h * cosT >= -r * 0.15 });
        }
        const s = splitPath(pts);
        front += s.front;
        back += s.back;
    }
    return { front, back, drawn: n, ry: R * cosT, wall: r * sinT };
}

/* ------------------------------------------------------------------ Alpine */

/** Ortak dönüş animasyonu: görünürken requestAnimationFrame ile phase artar; hareket azaltma tercihinde durur. */
function spin(speed = 0.9) {
    return {
        phase: 0,
        spinning: !window.matchMedia?.('(prefers-reduced-motion: reduce)').matches,
        _raf: null,
        _visible: true,
        startSpin() {
            this._io = new IntersectionObserver(([e]) => { this._visible = e.isIntersecting; });
            this._io.observe(this.$el);
            let last = performance.now();
            const loop = (now) => {
                const dt = Math.min(0.1, (now - last) / 1000);
                last = now;
                if (this.spinning && this._visible && !document.hidden) this.phase = (this.phase + dt * speed) % (Math.PI * 2000);
                this._raf = requestAnimationFrame(loop);
            };
            this._raf = requestAnimationFrame(loop);
        },
        destroy() { cancelAnimationFrame(this._raf); this._io?.disconnect(); },
    };
}

export function registerCoils(Alpine) {
    /** Sayfa sekmeleri (?bobin=…) */
    Alpine.data('coilsApp', (initial = 'solenoid') => ({
        tab: initial,
        go(key) {
            this.tab = key;
            const url = new URL(window.location);
            url.searchParams.set('bobin', key);
            history.replaceState(history.state, '', url);
        },
        copy(text) { window.copyText?.(text); },
    }));

    Alpine.data('solenoidCalc', () => ({
        ...spin(),
        mode: 'L', d: '10', len: '15', n: '20', target: '1µ', wire: '0,6', c: '',
        init() { this.startSpin(); },
        get D() { return parseSI(this.d); },
        get Len() { return parseSI(this.len); },
        get N() { return this.mode === 'L' ? parseSI(this.n) : solenoidTurns(this.D, this.Len, parseSI(this.target)); },
        get L() { return this.mode === 'L' ? solenoidL(this.D, this.Len, this.N) : parseSI(this.target); },
        get wireLen() { return ok(this.N, this.D) ? this.N * Math.PI * this.D / 1000 + 0.02 : NaN; },
        get rdc() { return copperResistance(this.wireLen, parseSI(this.wire)); },
        get minLen() { return ok(this.N, parseSI(this.wire)) ? this.N * parseSI(this.wire) : NaN; },
        get tooShort() { return this.minLen > this.Len * 1.001; },
        get f0() { return resonance(this.L, parseSI(this.c)); },
        get ratio() { return ok(this.D, this.Len) ? this.Len / this.D : NaN; },
        /** Çizim: uzunluk ve çap oranı korunarak 300×140 alana sığdırılır */
        get geo() {
            const D = ok(this.D) ? this.D : 10;
            const Len = ok(this.Len) ? this.Len : 15;
            const s = Math.min(250 / Len, 110 / D);
            const turns = ok(this.N) ? this.N : 0;
            return { ...helix({ turns, r: (D * s) / 2, len: Len * s, phase: this.phase }), r: (D * s) / 2, len: Len * s };
        },
    }));

    Alpine.data('multilayerCalc', () => ({
        ...spin(),
        mode: 'L', di: '8', dout: '16', len: '12', n: '120', target: '100µ', wire: '0,3', c: '',
        init() { this.startSpin(); },
        get Di() { return parseSI(this.di); },
        get Do() { return parseSI(this.dout); },
        get Len() { return parseSI(this.len); },
        get invalid() { return ok(this.Di, this.Do) && this.Do <= this.Di; },
        get N() { return this.mode === 'L' ? parseSI(this.n) : multilayerTurns(this.Di, this.Do, this.Len, parseSI(this.target)); },
        get L() { return this.mode === 'L' ? multilayerL(this.Di, this.Do, this.Len, this.N) : parseSI(this.target); },
        get layers() {
            const w = parseSI(this.wire);
            return ok(w, this.Di, this.Do) && this.Do > this.Di ? Math.max(1, Math.round((this.Do - this.Di) / 2 / w)) : NaN;
        },
        get perLayer() { return ok(this.N, this.layers) ? this.N / this.layers : NaN; },
        get wireLen() { return ok(this.N, this.Di, this.Do) ? this.N * Math.PI * (this.Di + this.Do) / 2 / 1000 + 0.02 : NaN; },
        get rdc() { return copperResistance(this.wireLen, parseSI(this.wire)); },
        get f0() { return resonance(this.L, parseSI(this.c)); },
        /** Katmanlar: içten dışa en fazla 4 sarmal, en dıştaki önde */
        get geo() {
            const Di = ok(this.Di) ? this.Di : 8;
            const Do = ok(this.Do) && this.Do > Di ? this.Do : Di * 2;
            const Len = ok(this.Len) ? this.Len : 12;
            const s = Math.min(240 / Len, 120 / Do);
            const shown = Math.min(4, ok(this.layers) ? this.layers : 3);
            const turns = ok(this.perLayer) ? Math.min(this.perLayer, 40) : 10;
            const ri = (Di * s) / 2;
            const ro = (Do * s) / 2;
            const layers = Array.from({ length: shown }, (_, i) => {
                const r = shown === 1 ? ro : ri + ((ro - ri) * (i + 0.5)) / shown;
                return { ...helix({ turns, r, len: Len * s, phase: this.phase + i * 0.7 }), r, i };
            });
            return { layers, ri, ro, len: Len * s, ex: ro * Math.sin(0.45) };
        },
    }));

    Alpine.data('toroidCalc', () => ({
        ...spin(0.5),
        mode: 'L', al: '100n', n: '20', target: '47µ', od: '20', id: '10', h: '7', wire: '0,5', c: '',
        presets: [['T50-2 (kırmızı)', '4,9n'], ['T50-6 (sarı)', '4,0n'], ['T37-2', '4,0n'], ['FT50-43', '523n'], ['FT37-43', '420n'], ['FT50-77', '1,1µ']],
        init() { this.startSpin(); },
        get AL() { return parseSI(this.al) * 1e9; }, // girilen H → nH/sarım²
        get N() { return this.mode === 'L' ? parseSI(this.n) : toroidTurns(this.AL, parseSI(this.target)); },
        get L() { return this.mode === 'L' ? toroidL(this.AL, this.N) : parseSI(this.target); },
        /** Bir sarımın tel uzunluğu ≈ 2·(yükseklik + (DÇ − İÇ)/2) */
        get perTurn() {
            const [od, id, h] = [parseSI(this.od), parseSI(this.id), parseSI(this.h)];
            return ok(od, id, h) && od > id ? 2 * (h + (od - id) / 2) + 2 * parseSI(this.wire || '0') : NaN;
        },
        get wireLen() { return ok(this.N, this.perTurn) ? this.N * this.perTurn / 1000 + 0.04 : NaN; },
        get rdc() { return copperResistance(this.wireLen, parseSI(this.wire)); },
        get fit() {
            const [id, w] = [parseSI(this.id), parseSI(this.wire)];
            return ok(id, w, this.N) ? Math.floor((Math.PI * (id - w)) / w) : NaN; // iç çevreye sığan tek kat sarım
        },
        get f0() { return resonance(this.L, parseSI(this.c)); },
        pick([, al]) { this.al = al; },
        get geo() {
            const od = ok(parseSI(this.od)) ? parseSI(this.od) : 20;
            const id = ok(parseSI(this.id)) && parseSI(this.id) < od ? parseSI(this.id) : od / 2;
            const s = 230 / od;
            const R = ((od + id) / 4) * s;
            const r = ((od - id) / 4) * s;
            const turns = ok(this.N) ? this.N : 0;
            return { ...toroid({ turns, R, r, phase: this.phase * 0.35 }), R, r };
        },
    }));

    Alpine.data('spiralCalc', () => ({
        ...spin(0.35),
        mode: 'L', di: '20', dout: '43', n: '10', target: '6,3µ', ferrite: '1,6', wire: '0,8', c: '',
        init() { this.startSpin(); },
        get Di() { return parseSI(this.di); },
        get Do() { return parseSI(this.dout); },
        get k() { const k = parseSI(this.ferrite); return ok(k) ? k : 1; },
        get invalid() { return ok(this.Di, this.Do) && this.Do <= this.Di; },
        /** Hava çekirdekli Wheeler değeri × ferrit katsayısı (Qi alıcılarında ferrit plaka endüktansı ~1,5–2 kat artırır) */
        get N() { return this.mode === 'L' ? parseSI(this.n) : spiralTurns(this.Di, this.Do, parseSI(this.target) / this.k); },
        get L() { return this.mode === 'L' ? spiralL(this.Di, this.Do, this.N) * this.k : parseSI(this.target); },
        get pitch() { return ok(this.N, this.Di, this.Do) ? (this.Do - this.Di) / 2 / this.N : NaN; },
        get gap() { return ok(this.pitch, parseSI(this.wire)) ? this.pitch - parseSI(this.wire) : NaN; },
        get wireLen() { return ok(this.N, this.Di, this.Do) ? this.N * Math.PI * (this.Di + this.Do) / 2 / 1000 + 0.03 : NaN; },
        get rdc() { return copperResistance(this.wireLen, parseSI(this.wire)); },
        get f0() { return resonance(this.L, parseSI(this.c)); },
        get geo() {
            const Do = ok(this.Do) ? this.Do : 43;
            const Di = ok(this.Di) && this.Di < Do ? this.Di : Do / 2;
            const s = 92 / (Do / 2);
            const turns = ok(this.N) ? this.N : 0;
            return { ...spiral({ n: turns, rIn: (Di / 2) * s, rOut: (Do / 2) * s }), rIn: (Di / 2) * s, rOut: (Do / 2) * s };
        },
        get tiltStyle() { return { transform: `perspective(900px) rotateX(58deg) rotateZ(${(this.phase * 57.3).toFixed(1)}deg)` }; },
    }));
}

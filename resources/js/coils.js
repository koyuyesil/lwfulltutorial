// Bobin hesaplayıcıları: endüktans formülleri ve 3D (izdüşümlü SVG) sarım çizimleri. Tamamı tarayıcıda, anlık.
//
// Yöntemler (yayınlanmış formüller; Coil64'ün kullandığı kaynaklar referans alınmıştır, kodu kopyalanmamıştır):
//  - Tek katman hava çekirdekli: Wheeler'ın sürekli formülünün R. Weaver uyarlaması + Rosa yuvarlak tel düzeltmesi (Ks, Km)
//  - Öz kapasite: Medhurst ampirik formülü · deri etkisi: yuvarlak telde asimptotik yaklaşım
//  - Çok katmanlı / folyo: Wheeler çok katmanlı formülü
//  - Ferrit toroid ve pot / E / U çekirdek: IEC 60205 etkin manyetik parametreler (C1, C2 → le, Ae, µe)
//  - PCB bobin: Mohan (1999) akım tabakası formülü · ferrit çubuk: A. Payne, "The Inductance of Ferrite Rod Antennas"
import { parseSI } from './calculators.js';

const MU0 = 4e-7 * Math.PI;
const IN = 25.4;            // mm / inç (Wheeler formülleri inç ile verilir)
const RHO_CU = 0.0172;      // bakır özdirenci, Ω·mm²/m (20 °C)
const ok = (...v) => v.every((x) => Number.isFinite(x) && x > 0);
const clamp = (v, lo, hi) => Math.min(hi, Math.max(lo, v));

/* ================================================================== Formüller (girişler mm, sonuç H) */

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
export function wheelerMulti(rMeanMm, lenMm, depthMm, n) {
    if (!ok(rMeanMm, lenMm, depthMm, n)) return NaN;
    const [a, b, c] = [rMeanMm / IN, lenMm / IN, depthMm / IN];
    return (0.8 * a * a * n * n) / (6 * a + 9 * b + 10 * c) * 1e-6;
}

export function multilayerL(diMm, doMm, lenMm, n) {
    if (!ok(diMm, doMm, lenMm, n) || doMm <= diMm) return NaN;
    return wheelerMulti((diMm + doMm) / 4, lenMm, (doMm - diMm) / 2, n);
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

export const finite = (v) => (Number.isFinite(v) ? v : NaN);

/**
 * Artan f(N) için f(N) = hedef olan N (ikiye bölme). Sınırlar içinde bulunamazsa NaN.
 * integer: en küçük tam sayı N (f(N) ≥ hedef)
 */
export function solveTurns(f, target, { lo = 0.5, hi = 1e6, integer = false } = {}) {
    if (!ok(target)) return NaN;
    if (integer) {
        for (let n = Math.max(1, Math.ceil(lo)); n <= Math.min(hi, 5000); n++) if (f(n) >= target) return n;
        return NaN;
    }
    let h = Math.max(lo * 2, 1);
    while (!(f(h) >= target) && h < hi) h *= 2;
    if (!(f(h) >= target)) return NaN;
    let l = lo;
    for (let i = 0; i < 70; i++) {
        const m = (l + h) / 2;
        if (f(m) >= target) h = m; else l = m;
    }
    return (l + h) / 2;
}

/* ------------------------------------------------------------------ Tek katman (hassas) */

/** Wheeler sürekli formülü (R. Weaver, ~%0,1): Dk tel merkezinden çap, len sarım uzunluğu (mm) */
export function weaverL(dkMm, lenMm, n) {
    if (!ok(dkMm, lenMm, n)) return NaN;
    const k = lenMm / dkMm;
    const sum = Math.log(1 + Math.PI / (2 * k)) + 1 / (2.3004 + 3.437 * k + 1.763 * k * k - 0.47 / (0.755 + 1 / k) ** 1.44);
    return 0.0002 * Math.PI * dkMm * n * n * sum * 1e-6;
}

/** Rosa yuvarlak tel düzeltmeleri: öz endüktans (x = adım / tel çapı) ve karşılıklı endüktans (n sarım) */
export function rosaKs(x) { return 1.25 - Math.log(2 * x); }
export function rosaKm(n) {
    const [n2, n3] = [n * n, n * n * n];
    return Math.log(2 * Math.PI) - 1.5 - Math.log(n) / (6 * n) - 0.33084236 / n - 1 / (120 * n3)
        + 1 / (504 * n3 * n2) - 0.0011923 / (n3 * n2 * n2) + 0.0005068 / (n3 * n3 * n3);
}

/** Yuvarlak telli tek katman: Dk (tel merkezinden çap), tel çapı, adım (mm), N sarım */
export function oneLayerL(dkMm, dwMm, pitchMm, n) {
    if (!ok(dkMm, dwMm, pitchMm, n)) return NaN;
    const L0 = weaverL(dkMm, n * pitchMm, n);
    // Akım tabakası → yuvarlak tel: µ0·a·N·(Ks + Km)
    const corr = 0.0002 * Math.PI * dkMm * n * (rosaKs(Math.max(pitchMm, dwMm) / dwMm) + rosaKm(Math.max(n, 1))) * 1e-6;
    return L0 - corr;
}

/** Medhurst öz kapasitesi (F): Dk ve uzunluk mm */
export function medhurstC(dkMm, lenMm) {
    if (!ok(dkMm, lenMm)) return NaN;
    const r = lenMm / dkMm;
    return (dkMm / 10) * (0.1126 * r + 0.08 + 0.27 / Math.sqrt(r)) * 1e-12;
}

/** Bakırda deri kalınlığı (mm) */
export function skinDepth(f) {
    return ok(f) ? Math.sqrt(1.72e-8 / (Math.PI * f * MU0)) * 1000 : NaN;
}

/** Yuvarlak tel AC / DC direnç oranı (yalnız deri etkisi; yakınlık etkisi hariç) */
export function skinFactor(dwMm, f) {
    const d = skinDepth(f);
    if (!ok(dwMm, d)) return 1;
    const x = dwMm / 2 / d;
    return x < 1.7 ? 1 + x ** 4 / 48 : x / 2 + 0.25 + 3 / (32 * x);
}

/* ------------------------------------------------------------------ Ferrit toroid (boyut + µ) */

/** IEC 60205 toroid: he (yuvarlatma düzeltmeli yükseklik), le, Ae (mm, mm²), AL (nH), L (H) */
export function ferriteToroid(odMm, idMm, hMm, mu, n, roundMm = 0) {
    if (!ok(odMm, idMm, hMm, mu) || odMm <= idMm) return null;
    const he = hMm * (1 - ((4 - Math.PI) * roundMm ** 2) / (hMm * (odMm - idMm) / 2));
    const ln = Math.log(odMm / idMm);
    const inv = 1 / idMm - 1 / odMm;
    const le = (Math.PI * ln) / inv;
    const Ae = (0.5 * he * ln * ln) / inv;
    const AL = 0.2 * he * mu * ln; // nH / sarım²
    return { he, le, Ae, AL, L: ok(n) ? AL * 1e-9 * n * n : NaN };
}

/* ------------------------------------------------------------------ PCB bobin (Mohan) */

export const PCB_SHAPES = {
    square: { label: 'Kare', sides: 4, c: [1.27, 2.07, 0.18, 0.13] },
    hex: { label: 'Altıgen', sides: 6, c: [1.09, 2.23, 0, 0.17] },
    oct: { label: 'Sekizgen', sides: 8, c: [1.07, 2.29, 0, 0.19] },
    circle: { label: 'Dairesel', sides: 0, c: [1.0, 2.46, 0, 0.2] },
};

/** Dış çap, ortalama çap, doluluk ve L. din iç çap, w iz genişliği, s aralık (mm) */
export function pcbCoil(shape, n, dinMm, wMm, sMm) {
    const sh = PCB_SHAPES[shape];
    if (!sh || !ok(n, wMm) || !(dinMm >= 0) || !(sMm >= 0)) return null;
    const dout = dinMm + 2 * (n * wMm + (n - 1) * sMm);
    const davg = (dout + dinMm) / 2;
    const rho = (dout - dinMm) / (dout + dinMm);
    const [c1, c2, c3, c4] = sh.c;
    const L = MU0 * n * n * (davg / 1000) * c1 / 2 * (Math.log(c2 / rho) + c3 * rho + c4 * rho * rho);
    // İz uzunluğu: her sarım, iç teğet çapı d olan çokgenin çevresi (n·d·tan(π/n)); daire: π·d
    const per = sh.sides ? sh.sides * Math.tan(Math.PI / sh.sides) : Math.PI;
    let len = 0;
    for (let i = 0; i < n; i++) len += per * (dinMm + wMm + 2 * i * (wMm + sMm));
    return { dout, davg, rho, L, lengthMm: len };
}

/* ------------------------------------------------------------------ Ferrit çubuk (Payne) */

/**
 * Lr, Dr: çubuk uzunluğu / çapı · dc: bobin gövdesi çapı · dw tel · p adım · offset: bobin merkezinin çubuk merkezine uzaklığı (mm)
 * Konum düzeltmesi basit yaklaşımdır (merkez 1 → çubuk ucunda ~0,65).
 */
export function ferriteRod({ Lr, Dr, mu, dc, dw, p, offset = 0, n }) {
    if (!ok(Lr, Dr, mu, dc, dw, p, n)) return null;
    const dk = dc + dw;
    const lc = n * p;
    if (lc >= Lr) return { L: NaN, ratio: NaN, lc, tooLong: true };
    const air = oneLayerL(dk, dw, p, n);
    let ratio = 1;
    if (mu > 1) {
        const canf = (5e-4 * Math.PI * (Lr - lc)) / (Math.log((2 * (Lr + Dr)) / Dr) - 1);
        const phi = 1 / (1 + ((Lr - lc) / Dr) ** 1.4 / (5 * mu));
        const k = (phi * canf + 2e-3 * Dr) / (2e-3 * dc);
        const lp = lc + 0.45 * dc;
        const x = (5.1 * (lp / dc)) / (1 + 2.8 * (dc / lp));
        const mufe = (mu - 1) * (Dr / dc) ** 2 + 1;
        ratio = (1 + x) / (1 / k + x / mufe);
    }
    const pos = 1 - 0.35 * clamp((2 * Math.abs(offset)) / Lr, 0, 1) ** 2;
    return { L: air * ratio * pos, air, ratio, pos, lc, tooLong: false };
}

/* ------------------------------------------------------------------ Pot / E / U çekirdek (IEC 60205) */

/** Yol parçaları (uzunluk mm, kesit mm²) → C1, C2, le, Ae */
export function coreConstants(parts) {
    let C1 = 0;
    let C2 = 0;
    for (const [l, A] of parts) {
        if (!(l > 0) || !(A > 0)) return null;
        C1 += l / A;
        C2 += l / (A * A);
    }
    return { C1, C2, le: (C1 * C1) / C2, Ae: C1 / C2 };
}

/** type: EE | EI | U | pot. dims mm. Döner: le, Ae, µe, AL (nH), L (H) */
export function ferriteCore(type, dims, mu, gapMm, n) {
    let parts = null;
    if (type === 'EE' || type === 'EI') {
        const { A, B, C, D, E, F } = dims;
        if (!ok(A, B, C, D, E, F) || E >= A || F >= E || D >= B) return null;
        const l1 = type === 'EI' ? D : 2 * D;
        const h = B - D;
        const s = F / 2;
        const p = (A - E) / 2;
        const A1 = 2 * C * p;
        const A2 = 2 * C * h;
        const A3 = 2 * s * C;
        parts = [[l1, A1], [E - F, A2], [l1, A3], [(Math.PI * (p + h)) / 4, (A1 + A2) / 2], [(Math.PI * (s + h)) / 4, (A2 + A3) / 2]];
    } else if (type === 'U') {
        const { A, B, C, D, E } = dims;
        if (!ok(A, B, C, D, E) || E >= A || D >= B) return null;
        const h = B - D;
        const p = (A - E) / 2;
        const A1 = C * p;
        const A2 = C * h;
        parts = [[2 * D, A1], [2 * E, A2], [2 * D, A1], [(Math.PI * (p + h)) / 4, (A1 + A2) / 2], [(Math.PI * (p + h)) / 4, (A1 + A2) / 2]];
    } else if (type === 'pot') {
        const { D1, D2, D3, D4, h1, h2, b } = dims;
        if (!ok(D1, D2, D3, h1, h2) || !(D4 >= 0) || !(b >= 0) || D2 >= D1 || D3 >= D2 || D4 >= D3 || h2 >= h1) return null;
        const [r1, r2, r3, r4] = [D4 / 2, D3 / 2, D2 / 2, D1 / 2];
        const h = (h1 - h2) / 2;
        const A1 = Math.PI * (r4 * r4 - r3 * r3) - 2 * b * (r4 - r3);           // dış duvar (yuvalar düşülür)
        const A3 = Math.PI * (r2 * r2 - r1 * r1);                               // orta ayak
        const k2 = 1 / (1 - (2 * b) / (2 * Math.PI * r3));
        // Taban plakası (radyal akış): l/A ve l/A² integralleri doğrudan
        const back1 = (k2 * Math.log(r3 / r2)) / (Math.PI * h);
        const back2 = (k2 * (r3 - r2)) / (2 * r3 * r2 * Math.PI * Math.PI * h * h);
        const s1 = r2 - Math.sqrt((r2 * r2 + r1 * r1) / 2);
        const s2 = Math.sqrt((r3 * r3 + r4 * r4) / 2) - r3;
        const l4 = (Math.PI * (2 * s2 + h)) / 4;
        const l5 = (Math.PI * (2 * s1 + h)) / 4;
        const A4 = (1 - (2 * b) / (Math.PI * (r3 + r4))) * Math.PI * (r4 * r4 - r3 * r3 + 2 * r3 * h) / 2;
        const A5 = Math.PI * (r2 * r2 - r1 * r1 + 2 * r2 * h) / 2;
        const c = coreConstants([[h2, A1], [h2, A3], [l4, A4], [l5, A5]]);
        if (!c || !(A1 > 0)) return null;
        const C1 = c.C1 + back1;
        const C2 = c.C2 + back2;
        parts = null;
        return finishCore({ C1, C2, le: (C1 * C1) / C2, Ae: C1 / C2 }, mu, gapMm, n);
    }
    const c = parts && coreConstants(parts);
    return c ? finishCore(c, mu, gapMm, n) : null;
}

function finishCore(c, mu, gapMm, n) {
    if (!ok(mu)) return null;
    const g = Math.max(0, gapMm || 0);
    const mue = mu / (1 + (g * mu) / c.le);
    const AL = (MU0 * mue) / (c.C1 * 1000) * 1e9; // nH / sarım²
    return { ...c, mue, AL, L: ok(n) ? AL * 1e-9 * n * n : NaN };
}

/* ================================================================== 3D geometri (izdüşüm) */

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

/** İşaretli üs: dikdörtgen kesitli sarım için (yuvarlatılmış kare) */
const spow = (v, e) => Math.sign(v) * Math.abs(v) ** e;

/**
 * Yatay eksenli sarmal (solenoid). yaw: düşey eksen etrafında dönüş (izleyiciye yan bakış).
 * rect: { ry, rz } verilirse yuvarlatılmış dikdörtgen kesit (ry düşey, rz derinlik yarı kenarı).
 */
export function helix({ turns, r, len, phase = 0, cx = 200, cy = 120, yaw = 0.45, steps = 36, rTo = null, rect = null }) {
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
        const y = rect ? rect.ry * spow(Math.cos(th), 0.3) : rad * Math.cos(th);
        const z = rect ? rect.rz * spow(Math.sin(th), 0.3) : rad * Math.sin(th);
        // Döndürülmüş derinlik −x·sinY + z·cosY; aynı x'teki eksen derinliğine göre önde olması z ≥ 0 demektir
        pts.push({ x: cx + x * cosY + z * sinY, y: cy + y, front: z >= 0 });
    }
    return { ...splitPath(pts), start: pts[0], end: pts[pts.length - 1], ex: r * sinY };
}

/**
 * Eksenel gövde (silindir ya da dikdörtgen prizma) izdüşümü: uçların x konumu, uç elipsi yarıçapları ve gövde dış hattı.
 * Sağ uç izleyiciye bakar (yaw > 0).
 */
export function axialBody({ r, len, cx = 200, cy = 120, yaw = 0.45, rect = null }) {
    const sinY = Math.sin(yaw);
    const cosY = Math.cos(yaw);
    const x1 = cx - (len / 2) * cosY;
    const x2 = cx + (len / 2) * cosY;
    const ry = rect ? rect.ry : r;
    const rx = (rect ? rect.rz : r) * sinY;
    const f = (v) => v.toFixed(1);
    const body = rect
        ? `M${f(x1 - rx)} ${f(cy - ry)} L${f(x2 + rx)} ${f(cy - ry)} L${f(x2 + rx)} ${f(cy + ry)} L${f(x1 - rx)} ${f(cy + ry)} Z`
        : `M${f(x1)} ${f(cy - ry)} L${f(x2)} ${f(cy - ry)} A${f(rx)} ${f(ry)} 0 0 1 ${f(x2)} ${f(cy + ry)} L${f(x1)} ${f(cy + ry)} A${f(rx)} ${f(ry)} 0 0 1 ${f(x1)} ${f(cy - ry)} Z`;
    const cap = rect
        ? `M${f(x2 - rx)} ${f(cy - ry)} L${f(x2 + rx)} ${f(cy - ry)} L${f(x2 + rx)} ${f(cy + ry)} L${f(x2 - rx)} ${f(cy + ry)} Z`
        : `M${f(x2 - rx)} ${f(cy)} A${f(rx)} ${f(ry)} 0 1 0 ${f(x2 + rx)} ${f(cy)} A${f(rx)} ${f(ry)} 0 1 0 ${f(x2 - rx)} ${f(cy)} Z`;
    return { x1, x2, rx, ry, body, cap };
}

/**
 * Bobinin manyetik alan çizgileri (yan görünüm, dipol biçimi). Her çizgi bobinin içinden sola → sağa (S → N) geçer,
 * N ucundan çıkıp bobinin dışından dolanarak S ucuna döner. Akış yönü yolun çizim yönüyle aynıdır.
 *  x1, x2: bobin uçları · r: iç yarıçap · count: bir taraftaki çizgi sayısı · spread: çizgiler arası açıklık
 *  reach: uçlardan dışarı taşma (ferrit çubukta daha uzun)
 */
export function fieldLines({ x1, x2, cy = 120, r, count = 3, spread = 22, reach = 26, top = 8, bottom = 232 }) {
    const lines = [];
    const cx = (x1 + x2) / 2;
    const f = (v) => v.toFixed(1);
    for (let i = 1; i <= count; i++) {
        for (const side of [-1, 1]) {
            const y0 = cy + side * r * 0.82 * (i / (count + 0.5));
            const peak = clamp(cy + side * (r + spread * i), top, bottom);
            const ex = reach + spread * 0.55 * i;
            // İçeride düz, dışarıda yumuşak dönüş (iki kübik eğri)
            const d = `M${f(x1)} ${f(y0)} L${f(x2)} ${f(y0)}`
                + ` C${f(x2 + ex)} ${f(y0)} ${f(x2 + ex)} ${f(peak)} ${f(cx)} ${f(peak)}`
                + ` C${f(x1 - ex)} ${f(peak)} ${f(x1 - ex)} ${f(y0)} ${f(x1)} ${f(y0)}`;
            lines.push({ d, i, side, arrows: [{ x: cx, y: y0, a: 0 }, { x: cx, y: peak, a: 180 }] });
        }
    }
    return lines;
}

/** Ok ucu (alan yönü): (x, y) noktasında a derece yönünde küçük üçgen */
export function arrowHead(x, y, a, s = 5) {
    const r = (a * Math.PI) / 180;
    const p = (dx, dy) => `${(x + dx * Math.cos(r) - dy * Math.sin(r)).toFixed(1)} ${(y + dx * Math.sin(r) + dy * Math.cos(r)).toFixed(1)}`;
    return `M${p(s, 0)} L${p(-s, -s * 0.75)} L${p(-s * 0.4, 0)} L${p(-s, s * 0.75)} Z`;
}

/** Endüktansa göre alan yoğunluğu: çizgi sayısı (2–5), görünürlük (0,35–0,9) ve akış süresi (s) */
export function fieldDensity(L) {
    const lg = Number.isFinite(L) && L > 0 ? Math.log10(L / 1e-9) : 0; // 1 nH → 0, 1 mH → 6
    const k = clamp(lg / 6, 0, 1);
    return { count: 2 + Math.round(k * 3), opacity: 0.35 + k * 0.55, speed: (2.4 - k * 1.4).toFixed(2) };
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

/** Çokgen spiral (PCB bobin): sides 0 → daire. rIn → rOut (iç teğet yarıçaplar), n sarım. */
export function polySpiral({ n, rIn, rOut, sides = 4 }) {
    if (!sides) return spiral({ n, rIn, rOut });
    const turns = Math.max(1, Math.min(Math.round(n), 60));
    const k = 1 / Math.cos(Math.PI / sides); // iç teğet → köşe yarıçapı
    const off = sides === 4 ? Math.PI / 4 : Math.PI / sides;
    let d = '';
    let start = null;
    let end = null;
    const total = turns * sides;
    for (let i = 0; i <= total; i++) {
        const t = i / total;
        const rad = (rIn + (rOut - rIn) * t) * k;
        const th = off + (2 * Math.PI * i) / sides;
        const x = rad * Math.cos(th);
        const y = rad * Math.sin(th);
        d += `${i ? ' L' : 'M'}${x.toFixed(1)} ${y.toFixed(1)}`;
        if (!i) start = { x, y };
        end = { x, y };
    }
    return { d, start, end };
}

/**
 * Düz bobinin (spiral / PCB) alan çizgileri: merkez delikten yukarı çıkar, dışarı açılıp kenarın ötesinde plakaya döner.
 * Yan görünüm, merkez (0,0); R: bobin dış yarıçapı. shielded: ferrit plaka varsa alt taraf çizilmez.
 */
export function flatFieldLines({ R, count = 3, height = 42, shielded = false, rx = 1 }) {
    const lines = [];
    const f = (v) => v.toFixed(1);
    for (let i = 1; i <= count; i++) {
        const w = R * (0.5 + 0.22 * i) * rx;
        const h = height * (0.5 + 0.3 * i);
        for (const side of [-1, 1]) {
            const x0 = side * R * 0.18 * (i / count) * rx;
            let d = `M${f(x0)} 0 C${f(x0)} ${f(-h)} ${f(side * w)} ${f(-h)} ${f(side * w)} 0`;
            if (!shielded) d += ` C${f(side * w)} ${f(h * 0.6)} ${f(x0)} ${f(h * 0.6)} ${f(x0)} 0`;
            lines.push({ d, i, side, arrows: [{ x: (x0 + side * w) / 2, y: -h * 0.75, a: side > 0 ? 0 : 180 }] });
        }
    }
    return lines;
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

/** Toroidin içinde dolaşan alan: çekirdek içinde eşmerkezli halkalar ve dönüş yönünde oklar */
export function toroidField({ R, r, tilt = 1.0, cx = 200, cy = 120, count = 3, turn = 0 }) {
    const cosT = Math.cos(tilt);
    return Array.from({ length: count }, (_, j) => {
        const rad = R + r * 0.75 * ((j + 1) / (count + 1) * 2 - 1);
        const ry = rad * cosT;
        const a = turn + j * 0.9;
        const arrows = [0, Math.PI].map((o) => {
            const th = a + o;
            return { x: cx + rad * Math.cos(th), y: cy + ry * Math.sin(th), ang: (Math.atan2(ry * Math.cos(th), -rad * Math.sin(th)) * 180) / Math.PI };
        });
        return { rx: rad, ry, arrows };
    });
}

/* ================================================================== Alpine */

/** Ortak hareket: tüm bobinlerde aynı hızlar. Eksenel bobinler yavaşça salınır, düzlemsel olanlar döner. */
export const MOTION = { swingSpeed: 0.6, swingAmp: 0.22, yaw0: 0.45, turnSpeed: 0.35 };
export const swingYaw = (t) => MOTION.yaw0 + MOTION.swingAmp * Math.sin(t * MOTION.swingSpeed);

/**
 * Hesaplayıcı nesnesine ortak animasyon ve önbellek ekler (getter'lar korunur).
 * t: saniye (görünürken ve "Döndür" açıkken artar) · memo: aynı karede tekrar hesaplamayı önler.
 */
function withSpin(obj) {
    const cache = {};
    const base = {
        t: 0,
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
                if (this.spinning && this._visible && !document.hidden) this.t += dt;
                this._raf = requestAnimationFrame(loop);
            };
            this._raf = requestAnimationFrame(loop);
        },
        destroy() { cancelAnimationFrame(this._raf); this._io?.disconnect(); },
        memo(name, deps, fn) {
            const key = JSON.stringify(deps);
            if (cache[name]?.k !== key) cache[name] = { k: key, v: fn() };
            return cache[name].v;
        },
        get yaw() { return swingYaw(this.t); },
        get turn() { return this.t * MOTION.turnSpeed; },
        /** Alan akışı da "Durdur" ile durur */
        get flowState() { return this.spinning ? 'running' : 'paused'; },
        get density() { return fieldDensity(this.L); },
        arrow: arrowHead,
    };
    Object.defineProperties(base, Object.getOwnPropertyDescriptors(obj));
    return base;
}

const num = (v) => parseSI(v);

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

    /** Eksenel bobin sahnesi: gövde, sarmal ve alan çizgileri (tek hesap, tüm bağlamalar paylaşır) */
    const axialScene = (self, { r, len, turns, rect = null, maxW = 250, maxH = 110, coreR = null }) => {
        const yaw = self.yaw;
        return self.memo('scene', [r, len, turns, rect, yaw, self.density.count], () => {
            const s = Math.min(maxW / len, maxH / (2 * Math.max(r, rect?.ry ?? 0, rect?.rz ?? 0)));
            const R = r * s;
            const L = len * s;
            const rs = rect ? { ry: rect.ry * s, rz: rect.rz * s } : null;
            const h = helix({ turns, r: R, len: L, yaw, rect: rs });
            const body = axialBody({ r: (coreR ?? r) * s * 0.92, len: L + 6, yaw, rect: rs ? { ry: rs.ry * 0.9, rz: rs.rz * 0.9 } : null });
            const field = fieldLines({ x1: body.x1, x2: body.x2, r: rs ? rs.ry : R, count: self.density.count });
            return { ...h, body, field, r: R, len: L, s, x1: body.x1, x2: body.x2 };
        });
    };

    Alpine.data('solenoidCalc', () => withSpin({
        mode: 'L', d: '10', len: '15', n: '20', target: '1µ', wire: '0,6', c: '',
        init() { this.startSpin(); },
        get D() { return num(this.d); },
        get Len() { return num(this.len); },
        get N() { return this.mode === 'L' ? num(this.n) : solenoidTurns(this.D, this.Len, num(this.target)); },
        get L() { return this.mode === 'L' ? solenoidL(this.D, this.Len, this.N) : num(this.target); },
        get wireLen() { return ok(this.N, this.D) ? this.N * Math.PI * this.D / 1000 + 0.02 : NaN; },
        get rdc() { return copperResistance(this.wireLen, num(this.wire)); },
        get minLen() { return ok(this.N, num(this.wire)) ? this.N * num(this.wire) : NaN; },
        get tooShort() { return this.minLen > this.Len * 1.001; },
        get f0() { return resonance(this.L, num(this.c)); },
        get ratio() { return ok(this.D, this.Len) ? this.Len / this.D : NaN; },
        get geo() {
            return axialScene(this, { r: ok(this.D) ? this.D / 2 : 5, len: ok(this.Len) ? this.Len : 15, turns: ok(this.N) ? this.N : 0 });
        },
    }));

    Alpine.data('multilayerCalc', () => withSpin({
        mode: 'L', di: '8', dout: '16', len: '12', n: '120', target: '100µ', wire: '0,3', c: '',
        init() { this.startSpin(); },
        get Di() { return num(this.di); },
        get Do() { return num(this.dout); },
        get Len() { return num(this.len); },
        get invalid() { return ok(this.Di, this.Do) && this.Do <= this.Di; },
        get N() { return this.mode === 'L' ? num(this.n) : multilayerTurns(this.Di, this.Do, this.Len, num(this.target)); },
        get L() { return this.mode === 'L' ? multilayerL(this.Di, this.Do, this.Len, this.N) : num(this.target); },
        get layers() {
            const w = num(this.wire);
            return ok(w, this.Di, this.Do) && this.Do > this.Di ? Math.max(1, Math.round((this.Do - this.Di) / 2 / w)) : NaN;
        },
        get perLayer() { return ok(this.N, this.layers) ? this.N / this.layers : NaN; },
        get wireLen() { return ok(this.N, this.Di, this.Do) ? this.N * Math.PI * (this.Di + this.Do) / 2 / 1000 + 0.02 : NaN; },
        get rdc() { return copperResistance(this.wireLen, num(this.wire)); },
        get f0() { return resonance(this.L, num(this.c)); },
        /** Katmanlar: içten dışa en fazla 4 sarmal, makara flanşları ve alan çizgileri */
        get geo() {
            const Di = ok(this.Di) ? this.Di : 8;
            const Do = ok(this.Do) && this.Do > Di ? this.Do : Di * 2;
            const Len = ok(this.Len) ? this.Len : 12;
            const shown = Math.min(4, ok(this.layers) ? this.layers : 3);
            const turns = ok(this.perLayer) ? Math.min(this.perLayer, 40) : 10;
            const yaw = this.yaw;
            return this.memo('geo', [Di, Do, Len, shown, turns, yaw, this.density.count], () => {
                const s = Math.min(230 / Len, 120 / Do);
                const ri = (Di * s) / 2;
                const ro = (Do * s) / 2;
                const L = Len * s;
                const layers = Array.from({ length: shown }, (_, i) => {
                    const r = shown === 1 ? ro : ri + ((ro - ri) * (i + 0.5)) / shown;
                    return { ...helix({ turns, r, len: L, yaw }), r, i };
                });
                const core = axialBody({ r: ri * 0.92, len: L, yaw });
                // Makara flanşları: uç elipsleri (sarımdan biraz büyük)
                const flange = axialBody({ r: ro + 12, len: L + 12, yaw });
                const field = fieldLines({ x1: flange.x1, x2: flange.x2, r: ri * 0.9, count: this.density.count, spread: 18, reach: 30 });
                return { layers, ri, ro, len: L, core, flange, field };
            });
        },
    }));

    Alpine.data('toroidCalc', () => withSpin({
        mode: 'L', al: '100n', n: '20', target: '47µ', od: '20', id: '10', h: '7', wire: '0,5', c: '',
        presets: [['T50-2 (kırmızı)', '4,9n'], ['T50-6 (sarı)', '4,0n'], ['T37-2', '4,0n'], ['FT50-43', '523n'], ['FT37-43', '420n'], ['FT50-77', '1,1µ']],
        init() { this.startSpin(); },
        get AL() { return num(this.al) * 1e9; }, // girilen H → nH/sarım²
        get N() { return this.mode === 'L' ? num(this.n) : toroidTurns(this.AL, num(this.target)); },
        get L() { return this.mode === 'L' ? toroidL(this.AL, this.N) : num(this.target); },
        /** Bir sarımın tel uzunluğu ≈ 2·(yükseklik + (DÇ − İÇ)/2) */
        get perTurn() {
            const [od, id, h] = [num(this.od), num(this.id), num(this.h)];
            return ok(od, id, h) && od > id ? 2 * (h + (od - id) / 2) + 2 * num(this.wire || '0') : NaN;
        },
        get wireLen() { return ok(this.N, this.perTurn) ? this.N * this.perTurn / 1000 + 0.04 : NaN; },
        get rdc() { return copperResistance(this.wireLen, num(this.wire)); },
        get fit() {
            const [id, w] = [num(this.id), num(this.wire)];
            return ok(id, w, this.N) ? Math.floor((Math.PI * (id - w)) / w) : NaN; // iç çevreye sığan tek kat sarım
        },
        get f0() { return resonance(this.L, num(this.c)); },
        pick([, al]) { this.al = al; },
        get geo() { return toroidGeo(this, num(this.od), num(this.id), this.N); },
    }));

    /** Toroid sahnesi (AL ve µ sekmeleri ortak) */
    const toroidGeo = (self, odIn, idIn, n) => {
        const od = ok(odIn) ? odIn : 20;
        const id = ok(idIn) && idIn < od ? idIn : od / 2;
        const turn = self.turn;
        return self.memo('geo', [od, id, n, turn, self.density.count], () => {
            const s = 230 / od;
            const R = ((od + id) / 4) * s;
            const r = ((od - id) / 4) * s;
            return { ...toroid({ turns: ok(n) ? n : 0, R, r, phase: turn }), R, r, field: toroidField({ R, r, count: self.density.count, turn }) };
        });
    };

    Alpine.data('spiralCalc', () => withSpin({
        mode: 'L', di: '20', dout: '43', n: '10', target: '6,3µ', ferrite: '1,6', wire: '0,8', c: '',
        init() { this.startSpin(); },
        get Di() { return num(this.di); },
        get Do() { return num(this.dout); },
        get k() { const k = parseSI(this.ferrite); return ok(k) ? k : 1; },
        get invalid() { return ok(this.Di, this.Do) && this.Do <= this.Di; },
        /** Hava çekirdekli Wheeler değeri × ferrit katsayısı (Qi alıcılarında ferrit plaka endüktansı ~1,5–2 kat artırır) */
        get N() { return this.mode === 'L' ? num(this.n) : spiralTurns(this.Di, this.Do, num(this.target) / this.k); },
        get L() { return this.mode === 'L' ? spiralL(this.Di, this.Do, this.N) * this.k : num(this.target); },
        get pitch() { return ok(this.N, this.Di, this.Do) ? (this.Do - this.Di) / 2 / this.N : NaN; },
        get gap() { return ok(this.pitch, num(this.wire)) ? this.pitch - num(this.wire) : NaN; },
        get wireLen() { return ok(this.N, this.Di, this.Do) ? this.N * Math.PI * (this.Di + this.Do) / 2 / 1000 + 0.03 : NaN; },
        get rdc() { return copperResistance(this.wireLen, num(this.wire)); },
        get f0() { return resonance(this.L, num(this.c)); },
        get geo() {
            const Do = ok(this.Do) ? this.Do : 43;
            const Di = ok(this.Di) && this.Di < Do ? this.Di : Do / 2;
            const turns = ok(this.N) ? this.N : 0;
            return this.memo('geo', [Di, Do, turns, this.density.count, this.k > 1], () => {
                const s = 92 / (Do / 2);
                return {
                    ...spiral({ n: turns, rIn: (Di / 2) * s, rOut: (Do / 2) * s }), rIn: (Di / 2) * s, rOut: (Do / 2) * s,
                    field: flatFieldLines({ R: (Do / 2) * s * 1.45, height: 62, count: this.density.count, shielded: this.k > 1 }),
                };
            });
        },
        get tiltStyle() { return flatTilt(this.turn); },
    }));

    /** Düz bobinlerin (spiral, PCB) ortak eğik görünümü: aynı eğim, aynı dönüş hızı */
    const flatTilt = (turn) => ({ transform: `perspective(900px) rotateX(58deg) rotateZ(${((turn * 180) / Math.PI).toFixed(1)}deg)` });

    /* -------------------------------------------------------------- yeni sekmeler */

    /** Hassas tek katman: adımlı sarım, Q, öz kapasite ve öz rezonans */
    Alpine.data('oneLayerCalc', () => withSpin({
        mode: 'L', d: '10', dw: '0,8', p: '1,2', n: '12', target: '1µ', f: '14M', c: '',
        init() { this.startSpin(); },
        get D() { return num(this.d); },
        get Dw() { return num(this.dw); },
        get P() { return Math.max(num(this.p), this.Dw); },
        get Dk() { return this.D + this.Dw; },
        get N() {
            return this.memo('N', [this.mode, this.d, this.dw, this.p, this.n, this.target], () => (this.mode === 'L'
                ? num(this.n) : solveTurns((x) => oneLayerL(this.Dk, this.Dw, this.P, x), num(this.target), { lo: 1 })));
        },
        get L() { return this.mode === 'L' ? oneLayerL(this.Dk, this.Dw, this.P, this.N) : num(this.target); },
        get Len() { return ok(this.N, this.P) ? this.N * this.P : NaN; },
        get wireLen() { return ok(this.N, this.Dk) ? (this.N * Math.hypot(Math.PI * this.Dk, this.P)) / 1000 + 0.02 : NaN; },
        get rdc() { return copperResistance(this.wireLen, this.Dw); },
        get rac() { return this.rdc * skinFactor(this.Dw, num(this.f)); },
        get Q() { return ok(this.L, this.rac, num(this.f)) ? (2 * Math.PI * num(this.f) * this.L) / this.rac : NaN; },
        get Cs() { return medhurstC(this.Dk, this.Len); },
        get srf() { return resonance(this.L, this.Cs); },
        /** Çalışma frekansında görünen endüktans (öz rezonansa yaklaştıkça artar) */
        get Leff() { const r = num(this.f) / this.srf; return Number.isFinite(r) && r < 1 ? this.L / (1 - r * r) : NaN; },
        get delta() { return skinDepth(num(this.f)); },
        get f0() { return resonance(this.L, num(this.c)); },
        get pitchError() { return num(this.p) < this.Dw; },
        get geo() {
            return axialScene(this, { r: ok(this.Dk) ? this.Dk / 2 : 5, len: ok(this.Len) ? this.Len : 15, turns: ok(this.N) ? this.N : 0 });
        },
        /** Tel kalınlığı çizimde ölçekli (sarımlar arası boşluk gerçek oranda görünür) */
        get wirePx() { return clamp(this.Dw * (this.geo?.s ?? 1), 1.4, 9); },
    }));

    /** Dikdörtgen / kare kesitli tek katman: eşdeğer alanlı daire çapıyla */
    Alpine.data('rectCalc', () => withSpin({
        mode: 'L', a: '10', b: '6', dw: '0,6', p: '0,8', n: '15', target: '1µ', c: '',
        init() { this.startSpin(); },
        get A() { return num(this.a); },
        get B() { return num(this.b); },
        get Dw() { return num(this.dw); },
        get P() { return Math.max(num(this.p), this.Dw); },
        /** Tel merkezinden geçen dikdörtgenle aynı alanlı dairenin çapı */
        get De() { return ok(this.A, this.B, this.Dw) ? 2 * Math.sqrt(((this.A + this.Dw) * (this.B + this.Dw)) / Math.PI) : NaN; },
        get N() {
            return this.memo('N', [this.mode, this.a, this.b, this.dw, this.p, this.n, this.target], () => (this.mode === 'L'
                ? num(this.n) : solveTurns((x) => oneLayerL(this.De, this.Dw, this.P, x), num(this.target), { lo: 1 })));
        },
        get L() { return this.mode === 'L' ? oneLayerL(this.De, this.Dw, this.P, this.N) : num(this.target); },
        get Len() { return ok(this.N, this.P) ? this.N * this.P : NaN; },
        get wireLen() { return ok(this.N, this.A, this.B) ? (this.N * 2 * (this.A + this.B + 2 * this.Dw)) / 1000 + 0.02 : NaN; },
        get rdc() { return copperResistance(this.wireLen, this.Dw); },
        get f0() { return resonance(this.L, num(this.c)); },
        get geo() {
            const a = ok(this.A) ? this.A : 10;
            const b = ok(this.B) ? this.B : 6;
            return axialScene(this, { r: Math.max(a, b) / 2, len: ok(this.Len) ? this.Len : 12, turns: ok(this.N) ? this.N : 0, rect: { ry: a / 2, rz: b / 2 } });
        },
    }));

    /** Çok katmanlı: dikdörtgen makara ya da bakır folyo */
    Alpine.data('rectMultiCalc', () => withSpin({
        kind: 'rect', mode: 'L', a: '12', b: '8', len: '15', dw: '0,4', n: '150', target: '200µ',
        d: '20', w: '20', ft: '0,1', ins: '0,05', nf: '20', c: '', // ft: folyo kalınlığı (t animasyon zamanıdır)
        init() { this.startSpin(); },
        /** Dikdörtgen makara: N sarım için katman ve sarım derinliği */
        build(n) {
            const [a, b, l, dw] = [num(this.a), num(this.b), num(this.len), num(this.dw)];
            if (!ok(a, b, l, dw, n)) return null;
            const perLayer = Math.max(1, Math.floor(l / dw));
            const layers = Math.ceil(n / perLayer);
            const c = layers * dw;
            const rm = Math.sqrt(((a + c) * (b + c)) / Math.PI); // ortalama sarım: eşdeğer alanlı daire yarıçapı
            return { perLayer, layers, c, rm, L: wheelerMulti(rm, l, c, n), wireM: (n * 2 * (a + b + 2 * c)) / 1000 + 0.02 };
        },
        foil(n) {
            const [d, w, t, ins] = [num(this.d), num(this.w), num(this.ft), num(this.ins)];
            if (!ok(d, w, t, n) || !(ins >= 0)) return null;
            const c = n * (t + ins);
            const dm = d + c;
            const len = (n * Math.PI * dm) / 1000;
            return { c, dm, L: wheelerMulti(dm / 2, w, c, n), wireM: len, rdc: (RHO_CU * len) / (w * t) };
        },
        get N() {
            const deps = [this.kind, this.mode, this.a, this.b, this.len, this.dw, this.n, this.d, this.w, this.ft, this.ins, this.nf, this.target];
            return this.memo('N', deps, () => {
                if (this.mode === 'L') return num(this.kind === 'foil' ? this.nf : this.n);
                const f = this.kind === 'foil' ? (x) => this.foil(x)?.L : (x) => this.build(x)?.L;
                return solveTurns(f, num(this.target), { lo: 1, integer: this.kind === 'foil' });
            });
        },
        get r() { return ok(this.N) ? (this.kind === 'foil' ? this.foil(this.N) : this.build(this.N)) : null; },
        get L() { return this.r?.L ?? NaN; },
        get wireLen() { return this.r?.wireM ?? NaN; },
        get rdc() { return this.kind === 'foil' ? (this.r?.rdc ?? NaN) : copperResistance(this.wireLen, num(this.dw)); },
        get f0() { return resonance(this.L, num(this.c)); },
        get geo() {
            const yaw = this.yaw;
            if (this.kind === 'foil') {
                const d = ok(num(this.d)) ? num(this.d) : 20;
                const w = ok(num(this.w)) ? num(this.w) : 20;
                const c = this.r?.c ?? 2;
                return this.memo('geo', ['foil', d, w, c, this.N, yaw, this.density.count], () => {
                    const s = Math.min(220 / w, 120 / (d + 2 * c));
                    const ro = ((d / 2 + c) * s);
                    const ri = (d / 2) * s;
                    const outer = axialBody({ r: ro, len: w * s, yaw });
                    const core = axialBody({ r: ri * 0.95, len: w * s + 14, yaw });
                    // Uç yüzde folyo katmanlarının spirali (elipse sıkıştırılmış)
                    const roll = spiral({ n: Math.min(this.N || 1, 24), rIn: ri, rOut: ro });
                    return { foil: true, outer, core, roll, sx: Math.sin(yaw), fx1: outer.x1 - outer.rx, fx2: outer.x2 + outer.rx, field: fieldLines({ x1: outer.x1, x2: outer.x2, r: ri * 0.9, count: this.density.count, spread: 16, reach: 28 }) };
                });
            }
            const r = this.r;
            const [a, b, l] = [num(this.a) || 12, num(this.b) || 8, num(this.len) || 15];
            const layers = Math.min(4, r?.layers ?? 2);
            const turns = Math.min(r?.perLayer ?? 10, 36);
            const c = r?.c ?? 2;
            return this.memo('geo', ['rect', a, b, l, layers, turns, c, yaw, this.density.count], () => {
                const s = Math.min(230 / l, 115 / (Math.max(a, b) + 2 * c));
                const rings = Array.from({ length: layers }, (_, i) => {
                    const g = (c * (i + 0.5)) / layers;
                    return { ...helix({ turns, r: 1, len: l * s, yaw, rect: { ry: ((a / 2 + g) * s), rz: ((b / 2 + g) * s) } }), i };
                });
                const core = axialBody({ r: 1, len: l * s + 14, yaw, rect: { ry: (a / 2) * s * 0.95, rz: (b / 2) * s * 0.95 } });
                return { foil: false, rings, core, fx1: core.x1 - core.rx, fx2: core.x2 + core.rx, field: fieldLines({ x1: core.x1, x2: core.x2, r: (a / 2) * s * 0.9, count: this.density.count, spread: 16, reach: 28 }) };
            });
        },
    }));

    /** Ferrit toroid: boyut ve geçirgenlikten (AL bilinmiyorken) */
    Alpine.data('ferriteToroidCalc', () => withSpin({
        mode: 'L', od: '22,1', id: '13,7', h: '7,9', mu: '2000', round: '0,5', n: '10', target: '100µ', wire: '0,5', bs: '0,35', c: '',
        init() { this.startSpin(); },
        core(n) { return ferriteToroid(num(this.od), num(this.id), num(this.h), num(this.mu), n, num(this.round) || 0); },
        get base() { return this.core(1); },
        get N() { return this.mode === 'L' ? num(this.n) : (this.base ? Math.sqrt(num(this.target) / (this.base.AL * 1e-9)) : NaN); },
        get L() { return this.mode === 'L' ? (this.core(this.N)?.L ?? NaN) : num(this.target); },
        /** Doyma akımı: B = µ0·µ·N·I / le → I = Bs·le / (µ0·µ·N) */
        get isat() { return ok(num(this.bs), this.base?.le, num(this.mu), this.N) ? (num(this.bs) * this.base.le / 1000) / (MU0 * num(this.mu) * this.N) : NaN; },
        get perTurn() {
            const [od, id, h] = [num(this.od), num(this.id), num(this.h)];
            return ok(od, id, h) && od > id ? 2 * (h + (od - id) / 2) + 2 * (num(this.wire) || 0) : NaN;
        },
        get wireLen() { return ok(this.N, this.perTurn) ? this.N * this.perTurn / 1000 + 0.04 : NaN; },
        get rdc() { return copperResistance(this.wireLen, num(this.wire)); },
        get f0() { return resonance(this.L, num(this.c)); },
        get geo() { return toroidGeo(this, num(this.od), num(this.id), this.N); },
    }));

    /** PCB bobin: kare / altıgen / sekizgen / dairesel spiral */
    Alpine.data('pcbCalc', () => withSpin({
        mode: 'L', shape: 'square', din: '5', w: '0,3', s: '0,3', n: '8', target: '1µ', oz: '1', c: '',
        shapes: PCB_SHAPES,
        init() { this.startSpin(); },
        coil(n) { return pcbCoil(this.shape, n, num(this.din), num(this.w), num(this.s)); },
        get N() {
            return this.memo('N', [this.mode, this.shape, this.din, this.w, this.s, this.n, this.target], () => (this.mode === 'L'
                ? Math.round(num(this.n)) : solveTurns((x) => this.coil(x)?.L ?? 0, num(this.target), { lo: 1, integer: true })));
        },
        get r() { return ok(this.N) ? this.coil(this.N) : null; },
        get L() { return this.r?.L ?? NaN; },
        get thick() { return (num(this.oz) || 1) * 0.035; },
        get rdc() { return this.r && ok(num(this.w)) ? (RHO_CU * this.r.lengthMm / 1000) / (num(this.w) * this.thick) : NaN; },
        get f0() { return resonance(this.L, num(this.c)); },
        get geo() {
            const r = this.r;
            const sh = PCB_SHAPES[this.shape];
            return this.memo('geo', [this.shape, r?.dout, this.din, this.N, this.w, this.density.count], () => {
                const dout = r?.dout ?? 15;
                const din = num(this.din) || 0;
                const s = 60 / (dout / 2);
                const w = clamp((num(this.w) || 0.3) * s, 1, 10);
                return {
                    ...polySpiral({ n: this.N || 1, rIn: (din / 2) * s + w / 2, rOut: (dout / 2) * s - w / 2, sides: sh?.sides ?? 4 }),
                    w, R: (dout / 2) * s, field: flatFieldLines({ R: (dout / 2) * s * 1.6, height: 62, count: this.density.count }),
                };
            });
        },
        get tiltStyle() { return flatTilt(this.turn); },
    }));

    /** Ferrit çubuk anten bobini */
    Alpine.data('rodCalc', () => withSpin({
        mode: 'L', lr: '100', dr: '10', mu: '400', dc: '11', dw: '0,4', p: '0,45', offset: '0', n: '40', target: '330µ', c: '',
        init() { this.startSpin(); },
        rod(n) {
            return ferriteRod({ Lr: num(this.lr), Dr: num(this.dr), mu: num(this.mu), dc: num(this.dc), dw: num(this.dw), p: Math.max(num(this.p), num(this.dw)), offset: num(this.offset) || 0, n });
        },
        get N() {
            return this.memo('N', [this.mode, this.lr, this.dr, this.mu, this.dc, this.dw, this.p, this.offset, this.n, this.target], () => (this.mode === 'L'
                ? num(this.n) : solveTurns((x) => this.rod(x)?.L || 0, num(this.target), { lo: 1, integer: true })));
        },
        get r() { return ok(this.N) ? this.rod(this.N) : null; },
        get L() { return this.r?.L ?? NaN; },
        get wireLen() { return ok(this.N, num(this.dc)) ? this.N * Math.PI * (num(this.dc) + num(this.dw)) / 1000 + 0.04 : NaN; },
        get rdc() { return copperResistance(this.wireLen, num(this.dw)); },
        get f0() { return resonance(this.L, num(this.c)); },
        get geo() {
            const [Lr, Dr, dc] = [num(this.lr) || 100, num(this.dr) || 10, num(this.dc) || 11];
            const lc = Math.min(this.r?.lc ?? 20, Lr * 0.95);
            const off = clamp(num(this.offset) || 0, -(Lr - lc) / 2, (Lr - lc) / 2);
            const yaw = this.yaw;
            return this.memo('geo', [Lr, Dr, dc, lc, off, this.N, yaw, this.density.count], () => {
                const s = Math.min(255 / Lr, 70 / dc);
                const rod = axialBody({ r: (Dr / 2) * s, len: Lr * s, yaw });
                const cxCoil = 200 + off * s * Math.cos(yaw);
                const coil = helix({ turns: Math.min(this.N || 1, 80), r: (dc / 2) * s + 1.5, len: lc * s, yaw, cx: cxCoil });
                // Ferrit alanı toplar: çizgiler çubuğun uçlarından çıkar, daha geniş dolaşır
                const field = fieldLines({ x1: rod.x1, x2: rod.x2, r: (Dr / 2) * s * 0.9, count: this.density.count, spread: 20, reach: 18 });
                return { rod, coil, field };
            });
        },
    }));

    /** Pot / E / U çekirdek */
    Alpine.data('coreCalc', () => withSpin({
        type: 'EE', mode: 'L', mu: '2000', g: '0', n: '30', target: '1m', c: '',
        A: '25', B: '12,8', C: '7,5', D: '8,7', E: '17,5', F: '7,5',
        D1: '18', D2: '14,9', D3: '7,6', D4: '3', h1: '10,6', h2: '7,2', bslot: '2',
        init() { this.startSpin(); },
        get dims() {
            return this.type === 'pot'
                ? { D1: num(this.D1), D2: num(this.D2), D3: num(this.D3), D4: num(this.D4) || 0, h1: num(this.h1), h2: num(this.h2), b: num(this.bslot) || 0 }
                : { A: num(this.A), B: num(this.B), C: num(this.C), D: num(this.D), E: num(this.E), F: num(this.F) };
        },
        get base() { return this.memo('base', [this.type, this.dims, this.mu, this.g], () => ferriteCore(this.type, this.dims, num(this.mu), num(this.g) || 0, 1)); },
        get N() { return this.mode === 'L' ? num(this.n) : (this.base ? Math.sqrt(num(this.target) / (this.base.AL * 1e-9)) : NaN); },
        get L() { return this.mode === 'L' ? (this.base && ok(this.N) ? this.base.AL * 1e-9 * this.N ** 2 : NaN) : num(this.target); },
        get f0() { return resonance(this.L, num(this.c)); },
        /** Çizim: çekirdek ön yüzü (yarılar), sarım bölgesi ve manyetik devre halkaları. Birim: mm → px */
        get geo() {
            const t = this.type;
            const d = this.dims;
            return this.memo('geo', [t, d, this.g, this.density.count], () => coreDrawing(t, d, num(this.g) || 0, this.density.count));
        },
        /** E / pot / U çizimleri düzlemsel: diğer bobinlerle aynı salınım, CSS ile */
        get swingStyle() { return { transform: `perspective(1000px) rotateX(10deg) rotateY(${(((this.yaw - MOTION.yaw0) * 180) / Math.PI * 1.4).toFixed(1)}deg)` }; },
    }));
}

/* ------------------------------------------------------------------ çekirdek çizimi */

/**
 * Çekirdek ön yüzü (SVG yolları), sarım alanları ve alan halkaları. Merkez (200, 120), en fazla 300×190 px.
 * EE / EI: E yarıları (EI'de alt yarı I) · pot: kesit görünümü (E'ye benzer, ortada delik) · U: iki U yarısı.
 */
export function coreDrawing(type, d, gap, count = 3) {
    const f = (v) => v.toFixed(1);
    const poly = (pts) => `M${pts.map(([x, y]) => `${f(x)} ${f(y)}`).join(' L')} Z`;
    let A;
    let B;
    let E;
    let F;
    let D;
    let hole = 0;
    if (type === 'pot') {
        ({ D1: A, D2: E, D3: F, D4: hole } = d);
        B = d.h1 / 2;
        D = d.h2 / 2;
    } else {
        ({ A, B, D, E } = d);
        F = type === 'U' ? 0 : d.F;
    }
    if (![A, B, D, E].every((v) => v > 0)) return null;
    const totalH = type === 'EI' ? B + (B - D) : 2 * B;
    const s = Math.min(300 / A, 180 / totalH);
    const cx = 200;
    const cy = 122 - (type === 'EI' ? (B - (B - D)) * s * 0 : 0);
    const g = Math.max(gap * s, gap > 0 ? 1.5 : 0); // hava boşluğu görünür olsun
    const X = (v) => cx + v * s;
    const Y = (v) => cy + v * s;
    const halves = [];
    const a2 = A / 2;
    const e2 = E / 2;
    const f2 = F / 2;
    if (type === 'U') {
        const top = [[-a2, -B], [a2, -B], [a2, 0], [e2, 0], [e2, -D], [-e2, -D], [-e2, 0], [-a2, 0]];
        halves.push(top, top.map(([x, y]) => [x, -y]));
    } else {
        const top = [[-a2, -B], [a2, -B], [a2, 0], [e2, 0], [e2, -D], [f2, -D], [f2, -gap / 2], [-f2, -gap / 2], [-f2, -D], [-e2, -D], [-e2, 0], [-a2, 0]];
        halves.push(top);
        if (type === 'EI') halves.push([[-a2, 0], [a2, 0], [a2, B - D], [-a2, B - D]]);
        else halves.push(top.map(([x, y]) => [x, -y]));
    }
    // Ayrık yarılar arasında boşluk gösterimi için küçük kaydırma
    const shapes = halves.map((pts, i) => poly(pts.map(([x, y]) => [X(x), Y(y) + (i ? g / 2 : -g / 2)])));
    const depth = { x: 14, y: -10 };
    const back = shapes.map((_, i) => poly(halves[i].map(([x, y]) => [X(x) + depth.x, Y(y) + depth.y + (i ? g / 2 : -g / 2)])));

    // Sarım bölgesi: E / pot'ta orta ayak çevresinde iki pencere; U'da sol bacakta
    const winH = type === 'EI' ? D : 2 * D;
    const wy = type === 'EI' ? -D : -D;
    const windows = type === 'U'
        ? [{ x: X(-e2) + 2, w: ((e2 - 0) * s) * 0.55, y: Y(-D) + 2, h: (2 * D) * s - 4 }]
        : [{ x: X(f2) + 2, w: (e2 - f2) * s - 4, y: Y(wy) + 2, h: winH * s - 4 }, { x: X(-e2) + 2, w: (e2 - f2) * s - 4, y: Y(wy) + 2, h: winH * s - 4 }];

    // Manyetik devre: orta ayaktan yukarı, boyunduruktan dışa, dış ayaktan aşağı (her pencere çevresinde bir halka ailesi)
    const yokeTop = Y(-(B + D) / 2);
    const yokeBot = type === 'EI' ? Y((B - D) / 2) : Y((B + D) / 2);
    const loops = [];
    const legs = type === 'U'
        ? [[X(-(a2 + e2) / 2), X((a2 + e2) / 2)]]
        : [[X(f2 * 0.5), X((a2 + e2) / 2)], [X(-f2 * 0.5), X(-(a2 + e2) / 2)]];
    for (const [inner, outer] of legs) {
        for (let i = 0; i < count; i++) {
            const k = (i - (count - 1) / 2) * 3.5;
            const xi = inner + (outer > inner ? k : -k) * 0.6;
            const xo = outer - (outer > inner ? k : -k) * 0.6;
            const yt = yokeTop - k * 0.5;
            const yb = yokeBot + k * 0.5;
            const rr = 10;
            const dir = outer > inner ? 1 : -1;
            // Yukarı (orta ayak) → dışa → aşağı (dış ayak) → içe
            const dpath = `M${f(xi)} ${f(yb - rr)} L${f(xi)} ${f(yt + rr)} Q${f(xi)} ${f(yt)} ${f(xi + dir * rr)} ${f(yt)} L${f(xo - dir * rr)} ${f(yt)}`
                + ` Q${f(xo)} ${f(yt)} ${f(xo)} ${f(yt + rr)} L${f(xo)} ${f(yb - rr)} Q${f(xo)} ${f(yb)} ${f(xo - dir * rr)} ${f(yb)} L${f(xi + dir * rr)} ${f(yb)} Q${f(xi)} ${f(yb)} ${f(xi)} ${f(yb - rr)} Z`;
            loops.push({ d: dpath, arrows: [{ x: xi, y: (yt + yb) / 2, a: -90 }, { x: xo, y: (yt + yb) / 2, a: 90 }] });
        }
    }
    const holeRect = type === 'pot' && hole > 0 ? { x: X(-hole / 2), w: hole * s, y: Y(-B), h: (2 * B) * s } : null;
    const gapY = type === 'U' ? null : { x: X(-f2), w: F * s, y: cy - g / 2, h: g };
    return { shapes, back, windows, loops, holeRect, gapY: gap > 0 ? gapY : null, s, cx, cy };
}

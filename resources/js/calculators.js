// Elektronik hesaplayıcılar ve birim dönüştürücüler (tamamı tarayıcıda, anlık).
// Değer girişleri SI önekleri ve servis notasyonunu anlar: 4k7, 2R2, 100n, 10µ, 1,5M …

/* ------------------------------------------------------------------ Yardımcılar */

const PREFIX = { p: 1e-12, n: 1e-9, u: 1e-6, 'µ': 1e-6, 'μ': 1e-6, m: 1e-3, R: 1, r: 1, k: 1e3, K: 1e3, M: 1e6, G: 1e9 };
const PREFIX_OUT = [
    [1e9, 'G'], [1e6, 'M'], [1e3, 'k'], [1, ''], [1e-3, 'm'], [1e-6, 'µ'], [1e-9, 'n'], [1e-12, 'p'],
];

/** "4k7" → 4700, "100n" → 1e-7, "2,2 kΩ" → 2200. Geçersizse NaN. */
export function parseSI(input) {
    if (input === null || input === undefined) return NaN;
    let s = String(input).trim().replace(/\s+/g, '').replace(',', '.');
    if (s === '') return NaN;

    // Servis notasyonu: 4k7, 2R2, 1M5, 4n7
    let m = s.match(/^(\d+)([RrpnuµμmkKMG])(\d+)[a-zA-ZΩ]*$/);
    if (m) return parseFloat(`${m[1]}.${m[3]}`) * PREFIX[m[2]];

    // Ondalık + önek + (birim)
    m = s.match(/^([+-]?(?:\d+\.?\d*|\.\d+)(?:e[+-]?\d+)?)([pnuµμmkKMG]?)(?:Ω|ohm|[a-zA-Z]{0,3})?$/i);
    if (!m) return NaN;
    // 'm' / 'M' büyük-küçük harf duyarlıdır; /i bayrağı yüzünden öneki orijinalden al
    const prefix = s.slice(m[1].length, m[1].length + m[2].length);
    const value = parseFloat(m[1]);
    return prefix ? value * (PREFIX[prefix] ?? 1) : value;
}

const trimZeros = (str) => (str.includes('.') && !str.includes('e') ? str.replace(/\.?0+$/, '') : str);

/** Türkçe ondalık ve binlik ayraç ile sayı. */
export function num(value, digits = 4) {
    if (!Number.isFinite(value)) return '—';
    if (value === 0) return '0';
    const abs = Math.abs(value);
    let out = abs >= 1e15 || abs < 1e-6 ? value.toExponential(digits - 1) : trimZeros(value.toPrecision(digits));
    if (!out.includes('e')) {
        const [int, dec] = out.split('.');
        out = int.replace(/\B(?=(\d{3})+(?!\d))/g, '.') + (dec ? ',' + dec : '');
    }
    return out;
}

/** 4700, 'Ω' → "4,7 kΩ" */
export function si(value, unit = '', digits = 4) {
    if (!Number.isFinite(value)) return '—';
    if (value === 0) return `0 ${unit}`.trim();
    const abs = Math.abs(value);
    const [mult, prefix] = PREFIX_OUT.find(([f]) => abs >= f * 0.9995) ?? PREFIX_OUT[PREFIX_OUT.length - 1];
    return `${trimZeros((value / mult).toPrecision(digits)).replace('.', ',')} ${prefix}${unit}`.trim();
}

/** SMD direnç kodu: 4.7 → "4R7", 10000 → "103", 10200 → "1022". Geçersizse null. */
export function smdCode(ohms) {
    if (!Number.isFinite(ohms) || ohms < 0) return null;
    if (ohms === 0) return '000';
    if (ohms < 10) {
        let s = trimZeros(ohms.toFixed(2));
        if (!s.includes('.')) s += '.0';
        if (s.startsWith('0.')) s = s.slice(1);
        return s.replace('.', 'R');
    }
    const e3 = Math.floor(Math.log10(ohms)) - 1;
    const s3 = Math.round(ohms / 10 ** e3);
    if (s3 < 100 && Math.abs(s3 * 10 ** e3 - ohms) < ohms * 1e-6) return `${String(s3).padStart(2, '0')}${e3}`;
    const e4 = Math.floor(Math.log10(ohms)) - 2;
    const s4 = Math.round(ohms / 10 ** e4);
    return e4 >= 0 ? `${String(s4).padStart(3, '0')}${e4}` : ohms.toFixed(1).replace('.', 'R');
}

/* E serileri */
export const SERIES = {
    E12: [1.0, 1.2, 1.5, 1.8, 2.2, 2.7, 3.3, 3.9, 4.7, 5.6, 6.8, 8.2],
    E24: [1.0, 1.1, 1.2, 1.3, 1.5, 1.6, 1.8, 2.0, 2.2, 2.4, 2.7, 3.0, 3.3, 3.6, 3.9, 4.3, 4.7, 5.1, 5.6, 6.2, 6.8, 7.5, 8.2, 9.1],
    E96: [1.00, 1.02, 1.05, 1.07, 1.10, 1.13, 1.15, 1.18, 1.21, 1.24, 1.27, 1.30, 1.33, 1.37, 1.40, 1.43, 1.47, 1.50, 1.54, 1.58, 1.62, 1.65, 1.69, 1.74, 1.78, 1.82, 1.87, 1.91, 1.96, 2.00, 2.05, 2.10, 2.15, 2.21, 2.26, 2.32, 2.37, 2.43, 2.49, 2.55, 2.61, 2.67, 2.74, 2.80, 2.87, 2.94, 3.01, 3.09, 3.16, 3.24, 3.32, 3.40, 3.48, 3.57, 3.65, 3.74, 3.83, 3.92, 4.02, 4.12, 4.22, 4.32, 4.42, 4.53, 4.64, 4.75, 4.87, 4.99, 5.11, 5.23, 5.36, 5.49, 5.62, 5.76, 5.90, 6.04, 6.19, 6.34, 6.49, 6.65, 6.81, 6.98, 7.15, 7.32, 7.50, 7.68, 7.87, 8.06, 8.25, 8.45, 8.66, 8.87, 9.09, 9.31, 9.53, 9.76],
};

/** En yakın standart değer. mode: 'nearest' | 'up' (bir üst değer). */
export function standard(value, series = 'E24', mode = 'nearest') {
    if (!(value > 0) || !Number.isFinite(value)) return NaN;
    const decade = Math.pow(10, Math.floor(Math.log10(value)));
    const candidates = [...SERIES[series].map((v) => v * decade), 10 * decade, SERIES[series].at(-1) * decade / 10];
    const fix = (v) => parseFloat(v.toPrecision(3));
    if (mode === 'up') {
        return fix(candidates.filter((c) => c >= value * 0.9999).sort((a, b) => a - b)[0]);
    }
    return fix(candidates.sort((a, b) => Math.abs(Math.log(a / value)) - Math.abs(Math.log(b / value)))[0]);
}

const ok = (...values) => values.every((v) => Number.isFinite(v));

/* ------------------------------------------------------------------ Araç tanımları */

export const TOOLS = [
    { key: 'ohm', group: 'Temel', title: 'Ohm kanunu', desc: 'Gerilim, akım, direnç ve güçten herhangi ikisini girin.', icon: 'Ω', keywords: 'v=ir gerilim akım direnç güç watt' },
    { key: 'divider', group: 'Temel', title: 'Gerilim bölücü', desc: 'Çıkış gerilimi ya da hedef gerilim için R2.', icon: '⫶', keywords: 'voltage divider r1 r2' },
    { key: 'network', group: 'Temel', title: 'Seri / paralel', desc: 'Direnç, kondansatör ve bobinlerin eşdeğer değeri.', icon: '∥', keywords: 'seri paralel eşdeğer direnç kapasite' },
    { key: 'led', group: 'Temel', title: 'LED ön direnci', desc: 'Besleme, LED gerilimi ve akımından direnç ve güç.', icon: '💡', keywords: 'led direnç akım' },
    { key: 'colors', group: 'Bileşen kodları', title: 'Direnç renk kodu', desc: '4, 5 ve 6 bantlı dirençler; değerden renge de çevirir.', icon: '🎨', keywords: 'renk bant color code' },
    { key: 'smd', group: 'Bileşen kodları', title: 'SMD direnç kodu', desc: '3 ve 4 haneli kodlar, R notasyonu ve EIA-96.', icon: '▭', keywords: 'smd 103 eia-96 01c' },
    { key: 'capcode', group: 'Bileşen kodları', title: 'Kondansatör kodu', desc: '104, 4n7, 222J gibi kodları değere çevirir.', icon: '⊣⊢', keywords: 'kapasitör kod 104 seramik' },
    { key: 'rc', group: 'Devre', title: 'RC zaman sabiti / filtre', desc: 'τ, şarj süreleri ve kesim frekansı.', icon: '⏱', keywords: 'rc tau filtre kesim alçak geçiren' },
    { key: 'reactance', group: 'Devre', title: 'Reaktans', desc: 'Belirli frekansta bobin ve kondansatör reaktansı, LC rezonansı.', icon: '∿', keywords: 'xl xc rezonans lc empedans' },
    { key: 'timer555', group: 'Devre', title: '555 astable', desc: 'Frekans, görev oranı ve darbe süreleri.', icon: '⎍', keywords: '555 osilatör astable frekans duty' },
    { key: 'db', group: 'Devre', title: 'dB / dBm', desc: 'Güç ve gerilim oranları, dBm ↔ mW.', icon: 'dB', keywords: 'desibel dbm mw kazanç' },
    { key: 'battery', group: 'Güç & batarya', title: 'Batarya süresi ve şarj', desc: 'Çalışma süresi, şarj süresi ve Wh.', icon: '🔋', keywords: 'mah wh pil şarj süresi' },
    { key: 'power', group: 'Güç & batarya', title: 'Güç tüketimi ve maliyet', desc: 'Watt → günlük / aylık kWh ve elektrik maliyeti.', icon: '⚡', keywords: 'kwh enerji maliyet fatura watt' },
    { key: 'trace', group: 'PCB & kablo', title: 'PCB yol genişliği', desc: 'IPC-2221: akım için gereken iz genişliği.', icon: '〰', keywords: 'pcb iz trace ipc 2221 bakır' },
    { key: 'awg', group: 'PCB & kablo', title: 'AWG ↔ mm²', desc: 'Kablo kesiti, çapı ve metre direnci.', icon: '⌀', keywords: 'awg kablo kesit mm2 çap' },
    { key: 'convert', group: 'Birim dönüştürücü', title: 'Birim dönüştürücü', desc: 'Uzunluk, veri, enerji, sıcaklık, basınç ve elektriksel birimler.', icon: '⇄', keywords: 'çevir dönüştür birim' },
];

/* ------------------------------------------------------------------ Birim dönüştürücü */

const linear = (units) => ({ units, toBase: (v, u) => v * units[u], fromBase: (v, u) => v / units[u] });

export const UNIT_CATEGORIES = {
    length: { label: 'Uzunluk', ...linear({ nm: 1e-9, 'µm': 1e-6, mm: 1e-3, cm: 1e-2, m: 1, km: 1e3, 'inç': 0.0254, ft: 0.3048, yd: 0.9144, mil: 1609.344, 'thou (mil)': 2.54e-5 }) },
    mass: { label: 'Kütle', ...linear({ mg: 1e-6, g: 1e-3, kg: 1, ton: 1e3, ons: 0.028349523125, lb: 0.45359237 }) },
    temperature: {
        label: 'Sıcaklık',
        units: { '°C': 1, '°F': 1, K: 1 },
        toBase: (v, u) => (u === '°C' ? v : u === '°F' ? (v - 32) * 5 / 9 : v - 273.15),
        fromBase: (v, u) => (u === '°C' ? v : u === '°F' ? v * 9 / 5 + 32 : v + 273.15),
    },
    data: { label: 'Veri', ...linear({ bit: 0.125, B: 1, KB: 1e3, MB: 1e6, GB: 1e9, TB: 1e12, KiB: 1024, MiB: 1024 ** 2, GiB: 1024 ** 3, TiB: 1024 ** 4 }) },
    rate: { label: 'Veri hızı', ...linear({ 'bit/s': 1, 'kbit/s': 1e3, 'Mbit/s': 1e6, 'Gbit/s': 1e9, 'B/s': 8, 'KB/s': 8e3, 'MB/s': 8e6, 'GB/s': 8e9 }) },
    energy: { label: 'Enerji', ...linear({ J: 1, kJ: 1e3, Wh: 3600, kWh: 3.6e6, cal: 4.184, kcal: 4184, eV: 1.602176634e-19 }) },
    power: { label: 'Güç', ...linear({ mW: 1e-3, W: 1, kW: 1e3, 'BG (PS)': 735.49875, hp: 745.69987158227 }) },
    pressure: { label: 'Basınç', ...linear({ Pa: 1, kPa: 1e3, bar: 1e5, psi: 6894.757293168, atm: 101325, mmHg: 133.322387415 }) },
    speed: { label: 'Hız', ...linear({ 'm/s': 1, 'km/sa': 1 / 3.6, mph: 0.44704, knot: 1852 / 3600 }) },
    area: { label: 'Alan', ...linear({ 'mm²': 1e-6, 'cm²': 1e-4, 'm²': 1, 'km²': 1e6, 'inç²': 0.00064516, 'ft²': 0.09290304, 'dönüm': 1e3, hektar: 1e4 }) },
    volume: { label: 'Hacim', ...linear({ mL: 1e-3, L: 1, 'm³': 1e3, 'galon (ABD)': 3.785411784, 'fl oz (ABD)': 0.0295735295625 }) },
    time: { label: 'Zaman', ...linear({ ns: 1e-9, 'µs': 1e-6, ms: 1e-3, s: 1, dk: 60, sa: 3600, 'gün': 86400, hafta: 604800 }) },
    frequency: { label: 'Frekans', ...linear({ Hz: 1, kHz: 1e3, MHz: 1e6, GHz: 1e9, 'dev/dk (rpm)': 1 / 60 }) },
    voltage: { label: 'Gerilim', ...linear({ 'µV': 1e-6, mV: 1e-3, V: 1, kV: 1e3 }) },
    current: { label: 'Akım', ...linear({ 'µA': 1e-6, mA: 1e-3, A: 1, kA: 1e3 }) },
    resistance: { label: 'Direnç', ...linear({ 'mΩ': 1e-3, 'Ω': 1, 'kΩ': 1e3, 'MΩ': 1e6 }) },
    capacitance: { label: 'Kapasite', ...linear({ pF: 1e-12, nF: 1e-9, 'µF': 1e-6, mF: 1e-3, F: 1 }) },
    inductance: { label: 'Endüktans', ...linear({ nH: 1e-9, 'µH': 1e-6, mH: 1e-3, H: 1 }) },
    charge: { label: 'Batarya kapasitesi', ...linear({ mAh: 3.6, Ah: 3600, C: 1 }) },
};

/* ------------------------------------------------------------------ Direnç renk kodu */

export const COLORS = [
    { name: 'Siyah', hex: '#111827', digit: 0, mult: 1, tc: 250 },
    { name: 'Kahverengi', hex: '#7c2d12', digit: 1, mult: 10, tol: 1, tc: 100 },
    { name: 'Kırmızı', hex: '#dc2626', digit: 2, mult: 100, tol: 2, tc: 50 },
    { name: 'Turuncu', hex: '#f97316', digit: 3, mult: 1e3, tc: 15 },
    { name: 'Sarı', hex: '#facc15', digit: 4, mult: 1e4, tc: 25 },
    { name: 'Yeşil', hex: '#16a34a', digit: 5, mult: 1e5, tol: 0.5, tc: 20 },
    { name: 'Mavi', hex: '#2563eb', digit: 6, mult: 1e6, tol: 0.25, tc: 10 },
    { name: 'Mor', hex: '#7c3aed', digit: 7, mult: 1e7, tol: 0.1, tc: 5 },
    { name: 'Gri', hex: '#6b7280', digit: 8, mult: 1e8, tol: 0.05, tc: 1 },
    { name: 'Beyaz', hex: '#f8fafc', digit: 9, mult: 1e9 },
    { name: 'Altın', hex: '#d4a017', mult: 0.1, tol: 5 },
    { name: 'Gümüş', hex: '#c0c0c0', mult: 0.01, tol: 10 },
];

const EIA96_MULT = { Z: 0.001, Y: 0.01, R: 0.01, X: 0.1, S: 0.1, A: 1, B: 10, H: 10, C: 100, D: 1e3, E: 1e4, F: 1e5 };

export function decodeSmd(code) {
    const c = String(code || '').trim().toUpperCase();
    if (!c) return null;
    let m;
    if ((m = c.match(/^(\d*)R(\d+)$/)) || (m = c.match(/^(\d+)R(\d*)$/))) {
        return { value: parseFloat(`${m[1] || '0'}.${m[2] || '0'}`), type: 'R notasyonu (R = ondalık nokta)' };
    }
    if ((m = c.match(/^(\d*)M(\d+)$/))) {
        return { value: parseFloat(`${m[1] || '0'}.${m[2]}`) / 1000, type: 'Milliohm notasyonu (M = ondalık, mΩ)' };
    }
    if ((m = c.match(/^(\d{2})([ZYRXSABHCDEF])$/))) {
        const idx = parseInt(m[1], 10);
        if (idx < 1 || idx > 96) return { error: 'EIA-96 kodu 01–96 arasında olmalı' };
        return { value: Math.round(SERIES.E96[idx - 1] * 100) * EIA96_MULT[m[2]], type: 'EIA-96 (%1 tolerans)' };
    }
    if ((m = c.match(/^(\d{2})(\d)$/))) return { value: parseInt(m[1], 10) * 10 ** parseInt(m[2], 10), type: '3 haneli (%5 tolerans)' };
    if ((m = c.match(/^(\d{3})(\d)$/))) return { value: parseInt(m[1], 10) * 10 ** parseInt(m[2], 10), type: '4 haneli (%1 tolerans)' };
    if (c === '0' || c === '000' || c === '0000') return { value: 0, type: 'Sıfır ohm köprü (jumper)' };
    return { error: 'Kod tanınmadı' };
}

export function decodeCapacitor(code) {
    const c = String(code || '').trim().toUpperCase().replace(',', '.');
    if (!c) return null;
    const tolMap = { B: '±0,1 pF', C: '±0,25 pF', D: '±0,5 pF', F: '±%1', G: '±%2', J: '±%5', K: '±%10', M: '±%20', Z: '+%80 / −%20' };
    let m = c.match(/^(.*?)([BCDFGJKMZ])$/);
    let tol = null;
    let body = c;
    if (m && /\d/.test(m[1]) && !/^\d+[PNU]\d*$/.test(c)) { body = m[1]; tol = tolMap[m[2]]; }

    if ((m = body.match(/^(\d{2})(\d)$/))) {
        const mult = parseInt(m[2], 10);
        const factor = mult === 8 ? 0.01 : mult === 9 ? 0.1 : 10 ** mult;
        return { farad: parseInt(m[1], 10) * factor * 1e-12, tol, type: '3 haneli kod (pF)' };
    }
    if ((m = body.match(/^(\d*)([PNU])(\d*)$/)) && (m[1] || m[3])) {
        const unit = { P: 1e-12, N: 1e-9, U: 1e-6 }[m[2]];
        return { farad: parseFloat(`${m[1] || '0'}.${m[3] || '0'}`) * unit, tol, type: 'Harf ondalık (4n7 = 4,7 nF)' };
    }
    if ((m = body.match(/^(\d+)R(\d+)$/))) return { farad: parseFloat(`${m[1]}.${m[2]}`) * 1e-12, tol, type: 'R notasyonu (pF)' };
    if ((m = body.match(/^\d{1,2}$/))) return { farad: parseInt(body, 10) * 1e-12, tol, type: 'Doğrudan pF' };
    return { error: 'Kod tanınmadı' };
}

/* ------------------------------------------------------------------ Alpine bileşenleri */

export function registerCalculators(Alpine) {
    // Şablonlarda kullanılan biçimlendiriciler
    Alpine.magic('si', () => si);
    Alpine.magic('num', () => num);
    Alpine.magic('parse', () => parseSI);

    Alpine.data('toolsApp', (initial = 'ohm') => ({
        tools: TOOLS,
        active: TOOLS.some((t) => t.key === initial) ? initial : 'ohm',
        query: '',
        favorites: JSON.parse(localStorage.getItem('tools.favorites') || '[]'),
        mobileList: false,

        get current() { return this.tools.find((t) => t.key === this.active); },
        get filtered() {
            const q = this.query.trim().toLocaleLowerCase('tr');
            return q ? this.tools.filter((t) => `${t.title} ${t.desc} ${t.keywords}`.toLocaleLowerCase('tr').includes(q)) : this.tools;
        },
        get groups() {
            const groups = {};
            const favs = this.filtered.filter((t) => this.favorites.includes(t.key));
            if (favs.length && !this.query) groups['★ Favoriler'] = favs;
            for (const t of this.filtered) (groups[t.group] ??= []).push(t);
            return groups;
        },
        open(key) {
            this.active = key;
            this.mobileList = false;
            const url = new URL(window.location);
            url.searchParams.set('arac', key);
            history.replaceState(history.state, '', url);
        },
        toggleFavorite(key) {
            this.favorites = this.favorites.includes(key) ? this.favorites.filter((k) => k !== key) : [...this.favorites, key];
            localStorage.setItem('tools.favorites', JSON.stringify(this.favorites));
        },
        copy(text) {
            navigator.clipboard?.writeText(String(text));
            this.$dispatch('toast', { message: `Kopyalandı: ${text}`, type: 'info', timeout: 1800 });
        },
    }));

    Alpine.data('ohmCalc', () => ({
        v: '', i: '', r: '', p: '',
        get given() { return ['v', 'i', 'r', 'p'].filter((k) => Number.isFinite(parseSI(this[k]))); },
        get result() {
            if (this.given.length !== 2) return null;
            let [V, I, R, P] = ['v', 'i', 'r', 'p'].map((k) => parseSI(this[k]));
            const has = (k) => this.given.includes(k);
            if (has('v') && has('i')) { R = V / I; P = V * I; }
            else if (has('v') && has('r')) { I = V / R; P = V * V / R; }
            else if (has('v') && has('p')) { I = P / V; R = V * V / P; }
            else if (has('i') && has('r')) { V = I * R; P = I * I * R; }
            else if (has('i') && has('p')) { V = P / I; R = P / (I * I); }
            else if (has('r') && has('p')) { V = Math.sqrt(P * R); I = Math.sqrt(P / R); }
            return { V, I, R, P };
        },
        clear() { this.v = this.i = this.r = this.p = ''; },
    }));

    Alpine.data('dividerCalc', () => ({
        mode: 'vout', vin: '5', r1: '10k', r2: '10k', vout: '3.3', series: 'E24',
        get out() {
            const [Vin, R1, R2] = [parseSI(this.vin), parseSI(this.r1), parseSI(this.r2)];
            if (!ok(Vin, R1, R2) || R1 + R2 <= 0) return null;
            const I = Vin / (R1 + R2);
            return { Vout: Vin * R2 / (R1 + R2), I, P1: I * I * R1, P2: I * I * R2, ratio: R2 / (R1 + R2) };
        },
        get solve() {
            const [Vin, R1, Vt] = [parseSI(this.vin), parseSI(this.r1), parseSI(this.vout)];
            if (!ok(Vin, R1, Vt) || Vt <= 0 || Vt >= Vin) return null;
            const R2 = R1 * Vt / (Vin - Vt);
            const std = standard(R2, this.series);
            const actual = Vin * std / (R1 + std);
            return { R2, std, actual, error: (actual - Vt) / Vt * 100 };
        },
    }));

    Alpine.data('networkCalc', () => ({
        kind: 'R', values: '1k, 2k2, 4k7',
        get unit() { return { R: 'Ω', C: 'F', L: 'H' }[this.kind]; },
        get parsed() { return this.values.split(/[\s;+]+|,(?=\s)|,(?![\d])/).map((v) => v.trim()).filter(Boolean).map(parseSI); },
        get invalid() { return this.parsed.some((v) => !Number.isFinite(v) || v <= 0); },
        get result() {
            const vals = this.parsed.filter((v) => Number.isFinite(v) && v > 0);
            if (!vals.length) return null;
            const sum = vals.reduce((a, b) => a + b, 0);
            const inv = 1 / vals.reduce((a, b) => a + 1 / b, 0);
            // Kondansatörde seri/paralel formülleri dirençtekinin tersidir
            return this.kind === 'C' ? { series: inv, parallel: sum, count: vals.length } : { series: sum, parallel: inv, count: vals.length };
        },
    }));

    Alpine.data('ledCalc', () => ({
        vs: '5', vf: '2', iF: '20m', count: 1, series: 'E24',
        presets: [['Kırmızı', 2.0], ['Sarı', 2.1], ['Yeşil', 2.2], ['Mavi', 3.2], ['Beyaz', 3.2], ['IR', 1.3]],
        get result() {
            const [Vs, Vf, I] = [parseSI(this.vs), parseSI(this.vf), parseSI(this.iF)];
            const n = Math.max(1, parseInt(this.count, 10) || 1);
            if (!ok(Vs, Vf, I) || I <= 0) return null;
            const drop = Vs - Vf * n;
            if (drop <= 0) return { error: `Besleme gerilimi ${n} LED için yetersiz (en az ${num(Vf * n, 3)} V gerekir).` };
            const R = drop / I;
            const std = standard(R, this.series, 'up');
            const Ireal = drop / std;
            const P = Ireal * Ireal * std;
            const rating = [0.125, 0.25, 0.5, 1, 2, 5].find((w) => w >= P * 2) ?? NaN;
            return { R, std, Ireal, P, rating, efficiency: (Vf * n) / Vs * 100 };
        },
    }));

    Alpine.data('colorCalc', () => ({
        colors: COLORS, bands: 4, b: [1, 0, 2, 10, 1, 1], reverse: '4k7',
        digitColors() { return COLORS.filter((c) => c.digit !== undefined); },
        multColors() { return COLORS.filter((c) => c.mult !== undefined); },
        tolColors() { return COLORS.filter((c) => c.tol !== undefined); },
        tcColors() { return COLORS.filter((c) => c.tc !== undefined); },
        get layout() {
            // Bant rolleri: d = rakam, m = çarpan, t = tolerans, c = sıcaklık katsayısı
            return { 4: ['d', 'd', 'm', 't'], 5: ['d', 'd', 'd', 'm', 't'], 6: ['d', 'd', 'd', 'm', 't', 'c'] }[this.bands];
        },
        optionsFor(role) { return { d: this.digitColors(), m: this.multColors(), t: this.tolColors(), c: this.tcColors() }[role]; },
        get value() {
            const roles = this.layout;
            let digits = '';
            let mult = 1;
            let tol = null;
            let tc = null;
            roles.forEach((role, i) => {
                const color = COLORS[this.b[i]];
                if (!color) return;
                if (role === 'd') digits += color.digit ?? '';
                if (role === 'm') mult = color.mult ?? 1;
                if (role === 't') tol = color.tol ?? null;
                if (role === 'c') tc = color.tc ?? null;
            });
            const R = parseInt(digits || '0', 10) * mult;
            return { R, tol, tc, min: tol ? R * (1 - tol / 100) : null, max: tol ? R * (1 + tol / 100) : null };
        },
        normalize() {
            // Bant sayısı değişince seçimleri geçerli renklere çek
            this.layout.forEach((role, i) => {
                const allowed = this.optionsFor(role).map((c) => COLORS.indexOf(c));
                if (!allowed.includes(this.b[i])) this.b[i] = allowed[role === 't' ? 0 : 0];
            });
        },
        applyReverse() {
            const R = parseSI(this.reverse);
            if (!(R > 0)) return;
            const digitsCount = this.bands === 4 ? 2 : 3;
            let exp = Math.floor(Math.log10(R)) - (digitsCount - 1);
            let sig = Math.round(R / 10 ** exp);
            if (sig >= 10 ** digitsCount) { sig = Math.round(sig / 10); exp++; }
            const digits = String(sig).padStart(digitsCount, '0').split('').map(Number);
            const multIndex = COLORS.findIndex((c) => c.mult !== undefined && Math.abs(Math.log10(c.mult) - exp) < 1e-9);
            if (multIndex < 0) return;
            digits.forEach((d, i) => { this.b[i] = COLORS.findIndex((c) => c.digit === d); });
            this.b[digitsCount] = multIndex;
            this.b[digitsCount + 1] = this.bands === 4 ? 10 : 1; // altın %5 / kahverengi %1
        },
    }));

    Alpine.data('smdCalc', () => ({
        code: '103',
        examples: ['103', '472', '4R7', '1002', '01C', '68X', 'R010', '000'],
        get result() { return decodeSmd(this.code); },
    }));

    Alpine.data('capCodeCalc', () => ({
        code: '104',
        examples: ['104', '473K', '222J', '4n7', '1R5', '330', '475', '109'],
        get result() { return decodeCapacitor(this.code); },
    }));

    Alpine.data('rcCalc', () => ({
        r: '10k', c: '100n',
        get result() {
            const [R, C] = [parseSI(this.r), parseSI(this.c)];
            if (!ok(R, C) || R <= 0 || C <= 0) return null;
            const tau = R * C;
            return { tau, fc: 1 / (2 * Math.PI * tau), t63: tau, t95: 3 * tau, t99: 5 * tau };
        },
    }));

    Alpine.data('reactanceCalc', () => ({
        f: '1k', l: '10m', c: '100n',
        get result() {
            const [f, L, C] = [parseSI(this.f), parseSI(this.l), parseSI(this.c)];
            return {
                XL: ok(f, L) ? 2 * Math.PI * f * L : NaN,
                XC: ok(f, C) && f > 0 && C > 0 ? 1 / (2 * Math.PI * f * C) : NaN,
                f0: ok(L, C) && L > 0 && C > 0 ? 1 / (2 * Math.PI * Math.sqrt(L * C)) : NaN,
            };
        },
    }));

    Alpine.data('timer555Calc', () => ({
        r1: '1k', r2: '10k', c: '10µ',
        get result() {
            const [R1, R2, C] = [parseSI(this.r1), parseSI(this.r2), parseSI(this.c)];
            if (!ok(R1, R2, C) || R1 <= 0 || R2 <= 0 || C <= 0) return null;
            const th = 0.693 * (R1 + R2) * C;
            const tl = 0.693 * R2 * C;
            return { f: 1 / (th + tl), duty: th / (th + tl) * 100, th, tl, period: th + tl };
        },
    }));

    Alpine.data('dbCalc', () => ({
        mode: 'power', a: '1', b: '2', dbm: '20', mw: '100',
        get ratioDb() {
            const [A, B] = [parseSI(this.a), parseSI(this.b)];
            if (!ok(A, B) || A <= 0 || B <= 0) return NaN;
            return (this.mode === 'power' ? 10 : 20) * Math.log10(B / A);
        },
        get fromDbm() { const d = parseSI(this.dbm); return Number.isFinite(d) ? 10 ** (d / 10) / 1000 : NaN; },
        get fromMw() { const m = parseSI(this.mw); return m > 0 ? 10 * Math.log10(m) : NaN; },
    }));

    Alpine.data('batteryCalc', () => ({
        capacity: '5000', voltage: '3.85', load: '350m', charge: '2', efficiency: 85,
        get result() {
            const capAh = parseSI(this.capacity) / 1000; // mAh girişi
            const [V, load, charge] = [parseSI(this.voltage), parseSI(this.load), parseSI(this.charge)];
            const eff = Math.min(100, Math.max(1, parseFloat(this.efficiency) || 85)) / 100;
            if (!ok(capAh) || capAh <= 0) return null;
            return {
                wh: ok(V) ? capAh * V : NaN,
                runtime: ok(load) && load > 0 ? capAh * eff / load : NaN,
                chargeTime: ok(charge) && charge > 0 ? capAh / (charge * eff) : NaN,
                cRate: ok(charge) ? charge / capAh : NaN,
            };
        },
        hours(h) {
            if (!Number.isFinite(h)) return '—';
            const total = Math.round(h * 60);
            const d = Math.floor(total / 1440);
            const hh = Math.floor((total % 1440) / 60);
            const mm = total % 60;
            return [d && `${d} gün`, hh && `${hh} sa`, `${mm} dk`].filter(Boolean).join(' ');
        },
    }));

    Alpine.data('powerCalc', () => ({
        watt: '65', hours: '8', days: '22', price: '2.6',
        get result() {
            const [W, h, d, p] = [parseSI(this.watt), parseSI(this.hours), parseSI(this.days), parseSI(this.price)];
            if (!ok(W, h)) return null;
            const daily = W * h / 1000;
            const monthly = daily * (ok(d) ? d : 30);
            return { daily, monthly, yearly: daily * 365, cost: ok(p) ? monthly * p : NaN, yearlyCost: ok(p) ? daily * 365 * p : NaN };
        },
    }));

    Alpine.data('traceCalc', () => ({
        current: '2', rise: '10', copper: '1', layer: 'external', length: '50',
        get result() {
            const [I, dT, oz, len] = [parseSI(this.current), parseSI(this.rise), parseSI(this.copper), parseSI(this.length)];
            if (!ok(I, dT, oz) || I <= 0 || dT <= 0 || oz <= 0) return null;
            const k = this.layer === 'external' ? 0.048 : 0.024;
            const areaMils2 = Math.pow(I / (k * Math.pow(dT, 0.44)), 1 / 0.725);
            const thicknessMils = oz * 1.378;
            const widthMils = areaMils2 / thicknessMils;
            const widthMm = widthMils * 0.0254;
            // Direnç: ρ(bakır) = 1,72e-8 Ω·m
            const areaM2 = (widthMm / 1000) * (thicknessMils * 0.0254 / 1000);
            const R = ok(len) ? 1.72e-8 * (len / 1000) / areaM2 : NaN;
            return { widthMils, widthMm, R, drop: R * I, loss: R * I * I };
        },
    }));

    Alpine.data('awgCalc', () => ({
        mode: 'awg', awg: '22', mm2: '0.5',
        fromAwg(n) {
            const d = 0.127 * Math.pow(92, (36 - n) / 39);
            const area = Math.PI / 4 * d * d;
            return { awg: n, d, area, rPerM: 0.0172 / area }; // Ω/m, bakır
        },
        get result() {
            if (this.mode === 'awg') {
                const n = parseFloat(String(this.awg).replace('/0', '').trim());
                const awg = String(this.awg).includes('/0') ? 1 - (parseInt(this.awg, 10) || 1) : n;
                return Number.isFinite(awg) ? this.fromAwg(awg) : null;
            }
            const A = parseSI(this.mm2);
            if (!(A > 0)) return null;
            const d = Math.sqrt(4 * A / Math.PI);
            const awg = 36 - 39 * Math.log(d / 0.127) / Math.log(92);
            return { awg, d, area: A, rPerM: 0.0172 / A, nearest: Math.round(awg) };
        },
    }));

    Alpine.data('unitConverter', () => ({
        categories: UNIT_CATEGORIES, cat: 'length', from: 'mm', to: 'inç', value: '1',
        get units() { return Object.keys(this.categories[this.cat].units); },
        setCat(cat) {
            this.cat = cat;
            const u = this.units;
            this.from = u[Math.min(1, u.length - 1)];
            this.to = u[Math.min(2, u.length - 1)];
        },
        swap() { [this.from, this.to] = [this.to, this.from]; },
        convert(to) {
            const v = parseFloat(String(this.value).replace(',', '.'));
            if (!Number.isFinite(v)) return NaN;
            const c = this.categories[this.cat];
            return c.fromBase(c.toBase(v, this.from), to);
        },
    }));
}

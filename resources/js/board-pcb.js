// Board ID devresinin canlı PCB görünümü (2D / gerçek CSS 3D).
// Sahne 520×320 birimlik sabit bir düzlemdir; kapsayıcı genişliğine göre ölçeklenir.
// Dirençler ve entegre, yan yüzleri olan kutulardır (translateZ ile yükseklik).
import { parseSI, si, smdCode } from './calculators.js';

const TYPES = {
    adc: { pin: 'ADC_ID', internal: false },
    logic: { pin: 'GPIO', internal: false },
    pullup: { pin: 'ADC_ID', internal: true },
};
const CHIPS = { pmic: 'PMIC', soc: 'CPU/SoC' };

const STAGE_W = 520;
const STAGE_H = 320;

const resistor = (value) => {
    if (value === null || value === undefined || String(value).trim() === '') return null;
    const v = typeof value === 'number' ? value : parseSI(value);
    return Number.isFinite(v) && v >= 0 ? v : NaN;
};

/**
 * R1'i ölçümle bulma: R2 sökülmeden ölçüm noktasında Vout ölçülür, VREF (Vin) bilinir.
 * Vout = Vin · R2 / (R1 + R2)  ⇒  R1 = R2 · (Vin − Vout) / Vout
 */
export function solveR1(vin, vout, r2) {
    if (![vin, vout, r2].every(Number.isFinite) || vout <= 0 || vout >= vin || r2 <= 0) return NaN;
    return r2 * (vin - vout) / vout;
}

/** Ohm → formdaki kısa yazım (3 anlamlı basamak): 99500 → "99.5k", 1200000 → "1.2M" */
export function shortOhm(v) {
    if (!Number.isFinite(v)) return '';
    const [div, suffix] = v >= 1e6 ? [1e6, 'M'] : v >= 1e3 ? [1e3, 'k'] : [1, ''];
    return String(Number((v / div).toPrecision(3))) + suffix;
}

export function registerBoardPcb(Alpine) {
    // Formdaki "R1 hesaplama" yardımcısı. form: () => ({ vref, r2, r2_org }) — $wire'dan okunur.
    Alpine.data('r1Calc', (form) => ({
        vin: '',
        vout: '',
        r2: '',
        init() {
            const f = form();
            this.vin = String(f.vref ?? '1.8');
            this.r2 = f.r2_org ? shortOhm(Number(f.r2_org)) : String(f.r2 ?? '');
        },
        get volts() { return { vin: parseFloat(String(this.vin).replace(',', '.')), vout: parseFloat(String(this.vout).replace(',', '.')) }; },
        get r2Value() { return resistor(this.r2); },
        get r1() { return solveR1(this.volts.vin, this.volts.vout, this.r2Value); },
        get ready() { return Number.isFinite(this.r1); },
        get error() {
            const { vin, vout } = this.volts;
            if (String(this.vout).trim() === '' || String(this.r2).trim() === '') return '';
            if (!Number.isFinite(this.r2Value) || this.r2Value === null) return 'R2 değeri okunamadı (örn. 10k, 4k7).';
            if (Number.isFinite(vout) && Number.isFinite(vin) && vout >= vin) return 'Vout, Vin\'den küçük olmalı; R2 açık devre ya da R1 yok olabilir.';
            if (Number.isFinite(vout) && vout <= 0) return 'Vout 0 V: R2 kısa devre ya da ölçüm hatalı olabilir.';
            return '';
        },
        /** Yeni takılacak R2 (formdaki R2) ile tamir sonrası beklenen ölçüm */
        expected(newR2) {
            const r2 = resistor(newR2);
            const { vin } = this.volts;
            return this.ready && Number.isFinite(r2) && r2 !== null ? vin * r2 / (this.r1 + r2) : NaN;
        },
        label(v) { return Number.isFinite(v) ? si(v, 'Ω', 3) : '—'; },
        volt(v) { return Number.isFinite(v) ? si(v, 'V', 3) : '—'; },
        short() { return shortOhm(this.r1); },
    }));

    Alpine.data('boardPcb', (source, options = {}) => ({
        source,
        mode: options.mode ?? '2d',
        rx: 0,
        rz: 0,
        dragging: null,
        scale: 1,
        stageW: STAGE_W,
        stageH: STAGE_H,

        init() {
            const fit = () => { this.scale = Math.max(0.3, (this.$refs.wrap?.clientWidth || STAGE_W) / STAGE_W); };
            fit();
            this._ro = new ResizeObserver(fit);
            this.$refs.wrap && this._ro.observe(this.$refs.wrap);
            if (this.mode === '3d') this.setMode('3d');
        },
        destroy() { this._ro?.disconnect(); },

        get p() { return this.source() || {}; },
        get meta() { return { ...(TYPES[this.p.type] ?? TYPES.adc), chip: CHIPS[this.p.chip] ?? CHIPS.pmic }; },
        get internal() { return this.meta.internal; },
        get vref() { const v = parseFloat(String(this.p.vref ?? '1.8').replace(',', '.')); return Number.isFinite(v) ? v : 1.8; },
        get r1() { return resistor(this.p.r1); },
        get r2() { return resistor(this.p.r2); },
        /** Onarım öncesi orijinal değerler (yalnızca bilgi; hesaba girmez) */
        get r1Org() { const v = resistor(this.p.r1_org); return Number.isFinite(v) ? v : null; },
        get r2Org() { const v = resistor(this.p.r2_org); return Number.isFinite(v) ? v : null; },
        get invalid() { return Number.isNaN(this.r1) || Number.isNaN(this.r2); },

        get vout() {
            const { r1, r2, vref } = this;
            if (this.invalid) return NaN;
            if (this.internal && r1 === null) return NaN; // dahili pull-up değeri bilinmiyor
            if (r1 !== null && r2 !== null) return r1 + r2 > 0 ? vref * r2 / (r1 + r2) : NaN;
            if (r1 !== null) return vref;
            if (r2 !== null) return 0;
            return NaN;
        },
        get level() {
            const v = this.vout;
            if (!Number.isFinite(v)) return null;
            return v >= this.vref * 0.7 ? 1 : v <= this.vref * 0.3 ? 0 : null;
        },

        code(r) { return r === null ? 'NC' : (Number.isNaN(r) ? '?' : smdCode(r)); },
        label(r) { return r === null ? 'Takılı değil' : (Number.isNaN(r) ? 'Geçersiz' : si(r, 'Ω', 3)); },
        orig(r) { return r === null ? '' : 'orj. ' + si(r, 'Ω', 3); },
        volts(v) { return Number.isFinite(v) ? si(v, 'V', 3) : '—'; },

        /* Görünüm */
        get is3d() { return this.mode === '3d'; },
        get sceneStyle() {
            const t = this.is3d ? `perspective(1300px) rotateX(${this.rx}deg) rotateZ(${this.rz}deg)` : 'none';
            return `width:${STAGE_W}px;height:${STAGE_H}px;transform:${t};transform-style:preserve-3d;${this.dragging ? 'transition:none' : ''}`;
        },
        /** 3D'de eğilen kart taşmasın diye sahne küçültülüp ortalanır. */
        get fit() { return this.is3d ? 0.8 : 1; },
        get stageStyle() {
            const s = this.scale * this.fit;
            const left = (STAGE_W * this.scale - STAGE_W * s) / 2;
            const top = this.is3d ? STAGE_H * this.scale * 0.06 : 0;
            return `width:${STAGE_W}px;height:${STAGE_H}px;transform:scale(${s});transform-origin:top left;left:${left}px;top:${top}px`;
        },
        get wrapHeight() { return `height:${STAGE_H * this.scale * (this.is3d ? 0.92 : 1)}px`; },
        /** Bileşen yüksekliği: 2D'de 0 (düz), 3D'de gerçek yükseklik */
        h(px) { return this.is3d ? px : 0; },

        setMode(mode) {
            this.mode = mode;
            [this.rx, this.rz] = mode === '3d' ? [56, -26] : [0, 0];
        },
        start(e) {
            if (!this.is3d) return;
            this.dragging = { x: e.clientX, y: e.clientY, rx: this.rx, rz: this.rz };
            e.currentTarget.setPointerCapture?.(e.pointerId);
        },
        move(e) {
            if (!this.dragging) return;
            this.rz = this.dragging.rz + (e.clientX - this.dragging.x) * 0.35;
            this.rx = Math.min(78, Math.max(0, this.dragging.rx - (e.clientY - this.dragging.y) * 0.35));
        },
        end() { this.dragging = null; },
    }));
}

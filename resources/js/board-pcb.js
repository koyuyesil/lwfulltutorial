// Board ID devresinin canlı PCB görünümü (2D / gerçek CSS 3D).
// Sahne 520×320 birimlik sabit bir düzlemdir; kapsayıcı genişliğine göre ölçeklenir.
// Dirençler ve entegre, yan yüzleri olan kutulardır (translateZ ile yükseklik).
import { parseSI, si, smdCode } from './calculators';

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

export function registerBoardPcb(Alpine) {
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

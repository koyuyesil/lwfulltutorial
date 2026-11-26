// Ortak arayüz yardımcıları: panoya kopyalama ve yatay kaydırmalı çip satırı.

/**
 * Metni panoya kopyalar. navigator.clipboard yalnızca güvenli bağlamda (https / localhost) vardır;
 * http://*.test gibi adreslerde gizli textarea + execCommand yedeğine düşer.
 */
export async function copyText(text) {
    const value = String(text ?? '');
    try {
        if (navigator.clipboard && window.isSecureContext) {
            await navigator.clipboard.writeText(value);
            return true;
        }
    } catch { /* yedeğe düş */ }

    const ta = document.createElement('textarea');
    ta.value = value;
    ta.setAttribute('readonly', '');
    ta.style.cssText = 'position:fixed;top:-1000px;opacity:0';
    document.body.appendChild(ta);
    ta.select();
    let ok = false;
    try { ok = document.execCommand('copy'); } catch { ok = false; }
    ta.remove();
    return ok;
}

/** Kopyala + bildirim. message verilmezse "Kopyalandı: …" */
export async function copyWithToast(text, message = null) {
    const ok = await copyText(text);
    window.dispatchEvent(new CustomEvent('toast', {
        detail: ok
            ? { message: message ?? `Kopyalandı: ${text}`, type: 'info', timeout: 1800 }
            : { message: 'Kopyalanamadı; metni seçip Ctrl+C ile kopyalayın.', type: 'error' },
    }));
    return ok;
}

export function registerUi(Alpine) {
    window.copyText = copyWithToast;

    /** Kopyalanabilir kod: tıklayınca kopyalar, kısa süre ✓ gösterir. */
    Alpine.data('copyCode', (code) => ({
        copied: false,
        async copy() {
            if (await copyWithToast(code)) {
                this.copied = true;
                setTimeout(() => { this.copied = false; }, 1500);
                return;
            }
            // Pano izni yoksa kodu seçili bırak: kullanıcı Ctrl+C ile kopyalar
            const el = this.$root.querySelector('[data-code]');
            if (el) {
                const range = document.createRange();
                range.selectNodeContents(el);
                const sel = window.getSelection();
                sel.removeAllRanges();
                sel.addRange(range);
            }
        },
    }));

    /**
     * Yatay kaydırmalı satır: kaydırma çubuğu gizli, kenarlarda yalnızca devamı varsa solma ve ok düğmesi,
     * fare tekerleğiyle yatay kaydırma, seçili öğe ([aria-current], [aria-pressed=true], [data-active]) görünür alana alınır.
     */
    Alpine.data('scrollRow', () => ({
        canLeft: false,
        canRight: false,

        init() {
            const track = this.$refs.track;
            this.update = this.update.bind(this);
            this._ro = new ResizeObserver(this.update);
            this._ro.observe(track);
            // Livewire yeniden çizince (seçim değişince) seçili öğeyi ortala
            this._mo = new MutationObserver(() => { this.update(); this.reveal(); });
            this._mo.observe(track, { subtree: true, attributes: true, attributeFilter: ['aria-current', 'aria-pressed', 'data-active', 'class'], childList: true });
            this.$nextTick(() => { this.update(); this.reveal(false); });
        },
        destroy() { this._ro?.disconnect(); this._mo?.disconnect(); },

        update() {
            const t = this.$refs.track;
            if (!t) return;
            this.canLeft = t.scrollLeft > 2;
            this.canRight = t.scrollLeft + t.clientWidth < t.scrollWidth - 2;
        },
        reveal(smooth = true) {
            const t = this.$refs.track;
            const el = t?.querySelector('[aria-current]:not([aria-current="false"]), [aria-pressed="true"], [data-active="true"]');
            if (!el || t.scrollWidth <= t.clientWidth) return;
            const left = el.offsetLeft - t.offsetLeft;
            const target = left - (t.clientWidth - el.offsetWidth) / 2;
            if (left < t.scrollLeft + 24 || left + el.offsetWidth > t.scrollLeft + t.clientWidth - 24) {
                t.scrollTo({ left: Math.max(0, target), behavior: smooth ? 'smooth' : 'auto' });
            }
        },
        by(dir) {
            const t = this.$refs.track;
            t.scrollBy({ left: dir * t.clientWidth * 0.7, behavior: 'smooth' });
        },
        wheel(e) {
            const t = this.$refs.track;
            if (t.scrollWidth <= t.clientWidth || Math.abs(e.deltaX) > Math.abs(e.deltaY) || e.shiftKey) return;
            // Satırın sonuna gelinince sayfa kaydırması devam etsin
            if ((e.deltaY < 0 && !this.canLeft) || (e.deltaY > 0 && !this.canRight)) return;
            e.preventDefault();
            t.scrollLeft += e.deltaY;
        },
        get mask() {
            const l = this.canLeft ? '36px' : '0px';
            const r = this.canRight ? '36px' : '0px';
            const g = `linear-gradient(to right, transparent 0, #000 ${l}, #000 calc(100% - ${r}), transparent 100%)`;
            return { maskImage: g, WebkitMaskImage: g };
        },
    }));
}

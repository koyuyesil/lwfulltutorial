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

/* ============================================================ Formlar (A3)
 * - Ctrl/Cmd + S: odaktaki (yoksa açık çekmecedeki ya da sayfadaki tek) Livewire formunu gönderir.
 * - Gönderimden sonra hata varsa ilk hatalı alana kaydırır, odaklar ve kısa süre vurgular.
 * - Kaydedilmemiş değişiklik göstergesi (x-ui.dirty) görünürken sayfadan ayrılmadan önce uyarır.
 * Gönderim sırasında düğmelerin kilitlenmesini Livewire wire:submit kendisi yapar.
 */
const isVisible = (el) => !!el && el.getClientRects().length > 0 && getComputedStyle(el).visibility !== 'hidden';

function targetForm() {
    const active = document.activeElement?.closest?.('form');
    if (active && isVisible(active)) return active;
    const forms = [...document.querySelectorAll('form[wire\\:submit], form[wire\\:submit\\.prevent]')].filter(isVisible);
    // Açık çekmece / modal içindeki form önceliklidir
    const inDialog = forms.filter((f) => f.closest('[role="dialog"], [aria-modal="true"]'));
    if (inDialog.length === 1) return inDialog[0];
    return forms.length === 1 ? forms[0] : null;
}

function focusFirstError(root) {
    const field = root.querySelector('[aria-invalid="true"]');
    const message = root.querySelector('[data-field-error]');
    const target = field ?? message;
    if (!target) return false;
    target.scrollIntoView({ behavior: matchMedia('(prefers-reduced-motion: reduce)').matches ? 'auto' : 'smooth', block: 'center' });
    const input = field ?? message.parentElement?.querySelector('input, select, textarea');
    input?.focus?.({ preventScroll: true });
    const box = input ?? target;
    box.classList.remove('ui-attention');
    void box.offsetWidth;
    box.classList.add('ui-attention');
    return true;
}

const hasDirtyChanges = () => [...document.querySelectorAll('[data-dirty-flag]')].some(isVisible);

export function registerForms() {
    if (window.__formsRegistered) return;
    window.__formsRegistered = true;

    document.addEventListener('keydown', (e) => {
        if (!(e.ctrlKey || e.metaKey) || e.altKey || e.key.toLowerCase() !== 's') return;
        const form = targetForm();
        if (!form) return;
        e.preventDefault();
        form.requestSubmit();
    });

    // Gönderilen formu hatırla; Livewire yanıtı işlendikten sonra hatalara bak
    let submitted = null;
    document.addEventListener('submit', (e) => {
        if (e.target instanceof HTMLFormElement) submitted = e.target;
    }, true);

    const hook = () => window.Livewire.hook('commit', ({ succeed }) => {
        succeed(() => requestAnimationFrame(() => {
            const form = submitted;
            if (!form) return;
            submitted = null;
            if (document.contains(form)) focusFirstError(form);
        }));
    });
    if (window.Livewire?.hook) hook();
    else document.addEventListener('livewire:init', hook, { once: true });

    window.addEventListener('beforeunload', (e) => {
        if (!hasDirtyChanges()) return;
        e.preventDefault();
        e.returnValue = '';
    });

    // wire:navigate ile sayfa değişirken
    document.addEventListener('livewire:navigate', (e) => {
        if (hasDirtyChanges() && !window.confirm('Kaydedilmemiş değişiklikleriniz var. Sayfadan ayrılmak istiyor musunuz?')) {
            e.preventDefault();
        }
    });
}
/* ============================================================ Yükleniyor durumları (A5)
 * Bir Livewire isteği 150 ms'yi aşarsa: sayfanın üstünde ince ilerleme çubuğu (#lw-progress) görünür,
 * isteği yapan bileşen data-busy alır ve tablo gövdesi / [data-list] alanı soluklaşır (CSS).
 * Arka plan yenilemeleri (wire:poll → $refresh) gösterge açmaz.
 */
export function registerLoading() {
    if (window.__loadingRegistered) return;
    window.__loadingRegistered = true;

    let active = 0;
    const bar = () => document.getElementById('lw-progress');
    const setBar = () => bar()?.toggleAttribute('hidden', active === 0);

    const hook = () => window.Livewire.hook('commit', ({ component, commit, respond }) => {
        const calls = commit?.calls ?? [];
        const background = calls.length > 0 && calls.every((c) => c.method === '$refresh') && Object.keys(commit?.updates ?? {}).length === 0;
        if (background) return;

        let shown = false;
        const timer = setTimeout(() => {
            shown = true;
            active++;
            component.el?.setAttribute('data-busy', '');
            setBar();
        }, 150);

        respond(() => {
            clearTimeout(timer);
            if (!shown) return;
            active = Math.max(0, active - 1);
            component.el?.removeAttribute('data-busy');
            setBar();
        });
    });

    if (window.Livewire?.hook) hook();
    else document.addEventListener('livewire:init', hook, { once: true });
}
// ApexCharts tabanlı Alpine bileşeni.
// Kullanım: <div x-data="apexChart(@js($options))"></div>
// Kütüphane yalnızca grafik içeren sayfalarda (dinamik import) yüklenir.

// Tema <html class="dark"> ile belirlenir (sisteme göre / açık / koyu seçimi)
const isDark = () => document.documentElement.classList.contains('dark');

const isPlainObject = (v) => v && typeof v === 'object' && !Array.isArray(v);

function merge(target, source) {
    const out = { ...target };
    for (const [key, value] of Object.entries(source ?? {})) {
        out[key] = isPlainObject(value) && isPlainObject(out[key]) ? merge(out[key], value) : value;
    }
    return out;
}

function baseOptions() {
    const dark = isDark();
    const muted = dark ? '#94a3b8' : '#64748b';

    return {
        chart: {
            fontFamily: 'Figtree, ui-sans-serif, system-ui, sans-serif',
            foreColor: muted,
            background: 'transparent',
            toolbar: { show: false },
            zoom: { enabled: false },
            animations: { speed: 450 },
            parentHeightOffset: 0,
        },
        theme: { mode: dark ? 'dark' : 'light' },
        grid: { borderColor: dark ? 'rgba(148,163,184,0.12)' : '#eef2f7', strokeDashArray: 4 },
        dataLabels: { enabled: false },
        legend: { position: 'top', horizontalAlign: 'right', fontWeight: 600, markers: { size: 5 } },
        tooltip: { theme: dark ? 'dark' : 'light' },
        stroke: { curve: 'smooth', width: 2.5 },
        noData: { text: 'Henüz veri yok', style: { color: muted } },
    };
}

export function registerCharts(Alpine) {
    Alpine.data('apexChart', (options = {}) => ({
        chart: null,

        async init() {
            const { default: ApexCharts } = await import('apexcharts');
            this.chart = new ApexCharts(this.$el, merge(baseOptions(), options));
            await this.chart.render();
            // Tema değişince grafik renklerini güncelle
            this._onTheme = () => {
                const b = baseOptions();
                this.chart?.updateOptions({ chart: { foreColor: b.chart.foreColor }, theme: b.theme, grid: b.grid, tooltip: b.tooltip }, false, false);
            };
            window.addEventListener('theme-changed', this._onTheme);
        },

        destroy() {
            window.removeEventListener('theme-changed', this._onTheme);
            this.chart?.destroy();
            this.chart = null;
        },
    }));
}

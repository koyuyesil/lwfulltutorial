// vCard QR kodu: telefon kamerasıyla okutulunca kişi rehbere eklenir.
// "qrcode" kütüphanesi yalnızca QR gösterilen sayfada yüklenir.

export function registerQr(Alpine) {
    Alpine.data('vcardQr', (cards = {}, version = '3.0', filename = 'kisi') => ({
        cards,
        version: cards[version] ? version : Object.keys(cards)[0],
        svg: '',
        error: '',
        large: false,

        init() {
            this.render();
            this.$watch('version', () => this.render());
        },

        async lib() {
            return (await import('qrcode')).default;
        },

        async render() {
            try {
                const QR = await this.lib();
                this.svg = await QR.toString(this.cards[this.version], {
                    type: 'svg', errorCorrectionLevel: 'M', margin: 1,
                    color: { dark: '#0f172a', light: '#ffffff' },
                });
                this.error = '';
            } catch (e) {
                this.error = 'QR kodu oluşturulamadı (veri çok uzun olabilir).';
                this.svg = '';
            }
        },

        get bytes() {
            return new TextEncoder().encode(this.cards[this.version] || '').length;
        },

        async downloadPng() {
            const QR = await this.lib();
            const url = await QR.toDataURL(this.cards[this.version], { errorCorrectionLevel: 'M', margin: 2, width: 1024 });
            const a = Object.assign(document.createElement('a'), { href: url, download: `${filename}-vcard-${this.version}.png` });
            a.click();
        },
    }));
}

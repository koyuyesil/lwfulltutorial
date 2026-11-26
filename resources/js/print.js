// Baskı sayfaları (servis fişi, termal fiş, cihaz etiketi): QR kodlarını çizer, sonra yazdırma penceresini açar.
import QRCode from 'qrcode';

async function renderQrs() {
    const nodes = [...document.querySelectorAll('[data-qr]')];
    await Promise.all(nodes.map(async (el) => {
        try {
            el.innerHTML = await QRCode.toString(el.dataset.qr, {
                type: 'svg', errorCorrectionLevel: 'M', margin: 0,
                color: { dark: '#000000', light: '#ffffff' },
            });
        } catch {
            el.textContent = 'QR oluşturulamadı';
        }
    }));
}

document.addEventListener('DOMContentLoaded', async () => {
    await renderQrs();
    if (document.body.dataset.autoprint === '1') {
        // Görseller yüklensin, sonra yazdır
        await Promise.all([...document.images].map((img) => (img.complete ? null : new Promise((r) => { img.onload = img.onerror = r; }))));
        setTimeout(() => window.print(), 150);
    }
});

// Ticket yazdırma ve PDF indirme yardımcıları.
// Eskiden CDN'den (unpkg/github) yükleniyordu; artık npm paketleri Vite ile,
// sadece ihtiyaç anında (dinamik import) yükleniyor.

const escapeHtml = (value) =>
    String(value ?? '').replace(/[&<>"']/g, (char) => ({
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#39;',
    })[char]);

const fallback = (value) => escapeHtml(value || 'N/A');

export function ticketHtml(ticket) {
    const client = ticket.client_product?.client ?? {};
    const product = ticket.client_product?.product ?? {};
    const clientName = client.company || `${client.first_name ?? ''} ${client.last_name ?? ''}`.trim() || 'N/A';

    // Müşteri verisi kullanıcı girdisidir: HTML'e basılmadan önce kaçışlanır (XSS önlemi).
    return `
        <div style="font-family: Arial, sans-serif; line-height: 1.6; font-size: 11px; color: #111;">
            <h2 style="text-align: center; color: #333;">Ticket #${fallback(ticket.id)}</h2>
            <hr style="margin: 20px 0; border: none; border-top: 1px solid #ccc;">
            <p><strong>Client:</strong> ${escapeHtml(clientName)}</p>
            <p><strong>Telefon:</strong> ${fallback(client.phone)}</p>
            <p><strong>E-posta:</strong> ${fallback(client.email)}</p>
            <p><strong>Adres:</strong> ${fallback(client.address)}</p>
            <p><strong>Manufacturer:</strong> ${fallback(product.manufacturer)}</p>
            <p><strong>Model Name:</strong> ${fallback(product.model_name)}</p>
            <p><strong>Serial / IMEI:</strong> ${fallback(ticket.client_product?.serial)} / ${fallback(ticket.client_product?.imei)}</p>
            <p><strong>Status:</strong> ${fallback(ticket.status)}</p>
            <p><strong>Priority:</strong> ${fallback(ticket.priority)}</p>
            <p><strong>Issue:</strong> ${fallback(ticket.problem)}</p>
            <hr style="margin: 20px 0; border: none; border-top: 1px solid #ccc;">
        </div>
    `;
}

export function printTicket(ticket) {
    const win = window.open('', '_blank', 'width=800,height=600');

    if (!win) {
        alert('Pop-up engelleyici açık, lütfen izin verin.');
        return;
    }

    win.document.write(`<!DOCTYPE html>
        <html lang="tr">
        <head>
            <meta charset="utf-8">
            <title>Ticket #${fallback(ticket.id)}</title>
            <style>body { margin: 20px; } p { margin: 5px 0; }</style>
        </head>
        <body>${ticketHtml(ticket)}</body>
        </html>`);
    win.document.close();
    win.focus();
    win.onafterprint = () => win.close();
    win.print();
}

export async function downloadTicketPdf(ticket) {
    const [{ jsPDF }, { default: html2canvas }] = await Promise.all([
        import('jspdf'),
        import('html2canvas'),
    ]);

    // jsPDF.html() html2canvas'ı global olarak arar
    window.html2canvas = html2canvas;

    const container = document.createElement('div');
    container.innerHTML = ticketHtml(ticket);

    const pdf = new jsPDF({ unit: 'mm', format: 'a4', compress: true });

    await pdf.html(container, {
        x: 10,
        y: 10,
        width: 190, // A4 genişliği (210mm) - kenar boşlukları
        windowWidth: 720,
    });

    pdf.save(`${ticket.id ?? 'ticket'}_A4.pdf`);
}

import './bootstrap';
import { printTicket, downloadTicketPdf } from './ticket-print';
import { registerCharts } from './charts';

// Blade şablonlarından (Alpine @click) erişilebilmesi için global yap
window.printTicket = printTicket;
window.downloadTicketPdf = downloadTicketPdf;

// Livewire'ın paketlediği Alpine başlamadan önce bileşenleri kaydet
if (window.Alpine) {
    registerCharts(window.Alpine);
} else {
    document.addEventListener('alpine:init', () => registerCharts(window.Alpine));
}

import './bootstrap';
import { printTicket, downloadTicketPdf } from './ticket-print';
import { registerCharts } from './charts';
import { registerCalculators } from './calculators';
import { registerQr } from './qr';
import { registerBoardPcb } from './board-pcb';

// Blade şablonlarından (Alpine @click) erişilebilmesi için global yap
window.printTicket = printTicket;
window.downloadTicketPdf = downloadTicketPdf;

// Livewire'ın paketlediği Alpine başlamadan önce bileşenleri kaydet
const register = (Alpine) => {
    registerCharts(Alpine);
    registerCalculators(Alpine);
    registerQr(Alpine);
    registerBoardPcb(Alpine);
};

if (window.Alpine) {
    register(window.Alpine);
} else {
    document.addEventListener('alpine:init', () => register(window.Alpine));
}

import './bootstrap';
import { printTicket, downloadTicketPdf } from './ticket-print';

// Blade şablonlarından (Alpine @click) erişilebilmesi için global yap
window.printTicket = printTicket;
window.downloadTicketPdf = downloadTicketPdf;

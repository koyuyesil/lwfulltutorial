import './bootstrap';
import { registerCharts } from './charts';
import { registerCalculators } from './calculators';
import { registerQr } from './qr';
import { registerBoardPcb } from './board-pcb';
import { registerUi } from './ui';
import { registerIntake } from './intake';

// Livewire'ın paketlediği Alpine başlamadan önce bileşenleri kaydet
const register = (Alpine) => {
    registerCharts(Alpine);
    registerCalculators(Alpine);
    registerQr(Alpine);
    registerBoardPcb(Alpine);
    registerUi(Alpine);
    registerIntake(Alpine);
};

if (window.Alpine) {
    register(window.Alpine);
} else {
    document.addEventListener('alpine:init', () => register(window.Alpine));
}

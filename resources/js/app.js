import './bootstrap';
import { registerCharts } from './charts';
import { registerCalculators } from './calculators';
import { registerCoils } from './coils';
import { registerQr } from './qr';
import { registerBoardPcb } from './board-pcb';
import { registerForms, registerLoading, registerUi } from './ui';
import { registerIntake } from './intake';

// Livewire'ın paketlediği Alpine başlamadan önce bileşenleri kaydet
const register = (Alpine) => {
    registerCharts(Alpine);
    registerCalculators(Alpine);
    registerCoils(Alpine);
    registerQr(Alpine);
    registerBoardPcb(Alpine);
    registerUi(Alpine);
    registerIntake(Alpine);
};

registerForms();
registerLoading();

if (window.Alpine) {
    register(window.Alpine);
} else {
    document.addEventListener('alpine:init', () => register(window.Alpine));
}

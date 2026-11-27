// Çalıştırma: node --test tests/js/
import { test } from 'node:test';
import assert from 'node:assert/strict';
import {
    solenoidL, solenoidTurns, multilayerL, multilayerTurns, toroidL, toroidTurns, spiralL, spiralTurns,
    resonance, copperResistance, helix, spiral, toroid, splitPath,
    weaverL, oneLayerL, rosaKm, medhurstC, skinFactor, ferriteToroid, pcbCoil, ferriteRod, ferriteCore, solveTurns,
    fieldLines, fieldDensity, flatFieldLines, axialBody, polySpiral, coreDrawing, swingYaw, MOTION,
} from '../../resources/js/coils.js';

const close = (a, b, rel = 1e-3) => assert.ok(Math.abs(a - b) <= Math.abs(b) * rel, `${a} ≈ ${b}`);

test('single layer solenoid follows Wheeler and inverts to turns', () => {
    // d = 1 in, l = 1 in, N = 10 → 100 / 58 µH
    close(solenoidL(25.4, 25.4, 10), 100 / 58 * 1e-6);
    close(solenoidTurns(25.4, 25.4, 100 / 58 * 1e-6), 10);
    assert.ok(Number.isNaN(solenoidL(0, 10, 10)));
});

test('multilayer, toroid and flat spiral formulas round trip', () => {
    const L = multilayerL(8, 16, 12, 120);
    assert.ok(L > 0);
    close(multilayerTurns(8, 16, 12, L), 120);
    assert.ok(Number.isNaN(multilayerL(16, 8, 12, 120))); // dış < iç

    close(toroidL(100, 10), 10e-6); // AL 100 nH/N², 10 sarım → 10 µH
    close(toroidTurns(100, 10e-6), 10);

    // Qi alıcı benzeri: 20–43 mm, 10 sarım → birkaç µH (hava çekirdek)
    const Ls = spiralL(20, 43, 10);
    assert.ok(Ls > 2e-6 && Ls < 6e-6);
    close(spiralTurns(20, 43, Ls), 10);
});

test('resonance and copper resistance', () => {
    close(resonance(10e-6, 100e-9), 1 / (2 * Math.PI * Math.sqrt(1e-12)));
    // 1 m, 0,5 mm bakır ≈ 0,0876 Ω
    close(copperResistance(1, 0.5), 0.0172 / (Math.PI * 0.0625), 1e-6);
    assert.ok(Number.isNaN(resonance(1e-6, 0)));
});

test('3D helpers split coils into front and back strokes', () => {
    const h = helix({ turns: 5, r: 30, len: 200 });
    assert.ok(h.front.startsWith('M') && h.back.startsWith('M'));
    // uç noktalar eksen boyunca: başlangıç solda, bitiş sağda
    assert.ok(h.start.x < h.end.x);
    // yarım sarımdan az olsa bile çizilir, 80'den fazlası sınırlanır
    assert.ok(helix({ turns: 500, r: 10, len: 100 }).front.split('M').length <= 82);

    const s = spiral({ n: 4, rIn: 10, rOut: 50 });
    assert.ok(Math.hypot(s.start.x, s.start.y) < Math.hypot(s.end.x, s.end.y));

    const t = toroid({ turns: 200, R: 60, r: 20 });
    assert.equal(t.drawn, 64);
    assert.ok(t.front.length > 0 && t.back.length > 0);

    const p = splitPath([{ x: 0, y: 0, front: true }, { x: 1, y: 0, front: true }, { x: 2, y: 0, front: false }]);
    assert.equal(p.front, 'M0.0 0.0 L1.0 0.0');
    assert.equal(p.back, 'M1.0 0.0 L2.0 0.0');
});

test('precise single layer: Weaver matches Nagaoka and Rosa correction lowers round wire inductance', () => {
    // D = l = 1 inç, 10 sarım: Nagaoka K = 0,6884 → 1,7256 µH
    close(weaverL(25.4, 25.4, 10), 1.7256e-6, 2e-3);
    const sheet = weaverL(10.8, 12 * 1.2, 12);
    const wire = oneLayerL(10.8, 0.8, 1.2, 12);
    assert.ok(wire < sheet && wire > sheet * 0.9);
    // Km büyük N'de ln(2π) − 1,5'e yaklaşır
    close(rosaKm(1e6), Math.log(2 * Math.PI) - 1.5, 1e-4);
    // Medhurst: l = D için H ≈ 0,4626 → D = 1 cm'de ≈ 0,46 pF
    close(medhurstC(10, 10), 0.4626e-12, 1e-3);
    assert.equal(skinFactor(0.1, 1e3), 1 + (0.05 / (Math.sqrt(1.72e-8 / (Math.PI * 1e3 * 4e-7 * Math.PI)) * 1000)) ** 4 / 48);
    assert.ok(skinFactor(1, 10e6) > 10);
    const n = solveTurns((x) => oneLayerL(10.8, 0.8, 1.2, x), wire, { lo: 1 });
    close(n, 12, 1e-6);
});

test('ferrite toroid, PCB coil, ferrite rod and gapped cores', () => {
    // µ = 2000, h = 10, DÇ/İÇ = e → AL = 0,2·10·2000·1 = 4000 nH
    const t = ferriteToroid(Math.E * 10, 10, 10, 2000, 10);
    close(t.AL, 4000);
    close(t.L, 4000e-9 * 100);
    assert.ok(ferriteToroid(20, 10, 7, 2000, 1, 1).AL < ferriteToroid(20, 10, 7, 2000, 1, 0).AL); // yuvarlatma kesiti azaltır

    const sq = pcbCoil('square', 8, 5, 0.3, 0.3);
    close(sq.dout, 5 + 2 * (8 * 0.3 + 7 * 0.3));
    assert.ok(sq.L > 0.3e-6 && sq.L < 2e-6); // ~ birkaç yüz nH – µH
    assert.ok(pcbCoil('circle', 8, 5, 0.3, 0.3).L < sq.L * 1.2);
    assert.equal(pcbCoil('triangle', 8, 5, 0.3, 0.3), null);

    const air = ferriteRod({ Lr: 100, Dr: 10, mu: 1, dc: 11, dw: 0.4, p: 0.45, n: 40 });
    const fe = ferriteRod({ Lr: 100, Dr: 10, mu: 400, dc: 11, dw: 0.4, p: 0.45, n: 40 });
    const end = ferriteRod({ Lr: 100, Dr: 10, mu: 400, dc: 11, dw: 0.4, p: 0.45, n: 40, offset: 40 });
    assert.ok(fe.L > air.L * 3, 'ferrit endüktansı birkaç kat artırır');
    assert.ok(end.L < fe.L, 'uca kaydırınca azalır');
    assert.ok(ferriteRod({ Lr: 10, Dr: 10, mu: 400, dc: 11, dw: 0.4, p: 0.45, n: 40 }).tooLong);

    const e = { A: 25, B: 12.8, C: 7.5, D: 8.7, E: 17.5, F: 7.5 };
    const ee = ferriteCore('EE', e, 2000, 0, 30);
    const gapped = ferriteCore('EE', e, 2000, 0.5, 30);
    assert.ok(ee.AL > 1000 && ee.AL < 3000, `E25 AL ${ee.AL}`); // datasheet E25/13/7 N87 ≈ 1900 nH
    assert.ok(gapped.mue < ee.mue && gapped.L < ee.L);
    assert.ok(ferriteCore('EI', e, 2000, 0, 1).le < ee.le);
    assert.ok(ferriteCore('pot', { D1: 18, D2: 14.9, D3: 7.6, D4: 3, h1: 10.6, h2: 7.2, b: 2 }, 2000, 0, 1).AL > 1000);
    assert.ok(ferriteCore('U', { A: 25, B: 16, C: 7, D: 10, E: 12 }, 2000, 0, 1).AL > 0);
    assert.equal(ferriteCore('EE', { ...e, E: 30 }, 2000, 0, 1), null);
});

test('field lines pass through the coil from S to N and return outside', () => {
    const lines = fieldLines({ x1: 100, x2: 300, r: 40, count: 3 });
    assert.equal(lines.length, 6);
    for (const l of lines) {
        assert.ok(l.d.startsWith('M100.0') && l.d.includes('L300.0'), 'içeride soldan sağa (S → N)');
        assert.equal(l.arrows[0].a, 0);
        assert.equal(l.arrows[1].a, 180);
        assert.ok(Math.abs(l.arrows[0].y - 120) < 40, 'iç kısım bobinin içinde');
        assert.ok(Math.abs(l.arrows[1].y - 120) > 40, 'dönüş bobinin dışında');
    }
    // Endüktans arttıkça çizgi sayısı ve görünürlük artar
    assert.ok(fieldDensity(1e-3).count > fieldDensity(10e-9).count);
    assert.ok(fieldDensity(1e-3).opacity > fieldDensity(10e-9).opacity);
    assert.ok(+fieldDensity(1e-3).speed < +fieldDensity(10e-9).speed);
    // Ferrit kalkanlı düz bobinde alan yalnızca üstte
    assert.ok(!flatFieldLines({ R: 90, shielded: true })[0].d.includes(' C', 30));
});

test('all coils share one motion model and geometry helpers are consistent', () => {
    assert.equal(swingYaw(0), MOTION.yaw0);
    assert.ok(Math.abs(swingYaw(2.6) - MOTION.yaw0) <= MOTION.swingAmp + 1e-9);
    const b = axialBody({ r: 30, len: 200, yaw: 0.45 });
    assert.ok(b.x1 < 200 && b.x2 > 200 && b.rx > 0 && b.body.startsWith('M'));
    const h = helix({ turns: 6, r: 30, len: 200, yaw: 0.45 });
    // Sarmalın uçları gövde uçlarıyla aynı yerde
    assert.ok(Math.abs(h.start.x - b.x1) < b.rx + 1 && Math.abs(h.end.x - b.x2) < b.rx + 1);
    const sq = polySpiral({ n: 3, rIn: 10, rOut: 40, sides: 4 });
    assert.ok(sq.d.split('L').length === 3 * 4 + 1);
    const core = coreDrawing('EE', { A: 25, B: 12.8, C: 7.5, D: 8.7, E: 17.5, F: 7.5 }, 0.5, 3);
    assert.equal(core.shapes.length, 2);
    assert.equal(core.loops.length, 6);
    assert.ok(core.gapY.h >= 1.5);
    assert.equal(coreDrawing('EE', { A: 0, B: 1, C: 1, D: 1, E: 1, F: 1 }, 0), null);
});
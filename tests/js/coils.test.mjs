// Çalıştırma: node --test tests/js/
import { test } from 'node:test';
import assert from 'node:assert/strict';
import {
    solenoidL, solenoidTurns, multilayerL, multilayerTurns, toroidL, toroidTurns, spiralL, spiralTurns,
    resonance, copperResistance, helix, spiral, toroid, splitPath,
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

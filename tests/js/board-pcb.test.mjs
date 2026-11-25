// Çalıştırma: node --test tests/js/
import { test } from 'node:test';
import assert from 'node:assert/strict';
import { solveR1, shortOhm } from '../../resources/js/board-pcb.js';

test('R1 is solved from measured Vout without removing R2', () => {
    // 1,8 V, R1 = 100k, R2 = 10k → Vout = 0,1636 V
    const vout = 1.8 * 10000 / (100000 + 10000);
    assert.ok(Math.abs(solveR1(1.8, vout, 10000) - 100000) < 1e-6);
    assert.ok(Math.abs(solveR1(1.8, 0.9, 47000) - 47000) < 1e-6);
});

test('impossible measurements return NaN', () => {
    assert.ok(Number.isNaN(solveR1(1.8, 1.8, 10000)));  // R2 açık / R1 yok
    assert.ok(Number.isNaN(solveR1(1.8, 0, 10000)));    // kısa devre
    assert.ok(Number.isNaN(solveR1(1.8, 0.5, 0)));
});

test('ohms are written in the short form the form accepts', () => {
    assert.equal(shortOhm(100000), '100k');
    assert.equal(shortOhm(99512), '99.5k');
    assert.equal(shortOhm(4700), '4.7k');
    assert.equal(shortOhm(1200000), '1.2M');
    assert.equal(shortOhm(470), '470');
    assert.equal(shortOhm(NaN), '');
});

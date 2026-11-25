// Çalıştırma: node --test tests/js/
import { test } from 'node:test';
import assert from 'node:assert/strict';
import { flowDuration, heat, logPos, reactanceChart, signalBars, bandPositions, num } from '../../resources/js/calculators.js';

test('current flow animation speeds up with current and stops without it', () => {
    assert.equal(flowDuration(0), 0);
    assert.equal(flowDuration(NaN), 0);
    assert.ok(flowDuration(1) < flowDuration(0.02));
    assert.ok(flowDuration(0.02) < flowDuration(1e-6));
});

test('resistor heat is relative to a half watt', () => {
    assert.equal(heat(0), 0);
    assert.equal(heat(0.25), 0.5);
    assert.equal(heat(5), 1);
});

test('log position spans decades', () => {
    assert.equal(logPos(1e-9, 1e-12, 1e-2), 0.3);
    assert.ok(Number.isNaN(logPos(0, 1, 10)));
});

test('reactance chart crosses at resonance', () => {
    const c = reactanceChart(1000, 10e-3, 100e-9, 360, 200);
    assert.ok(c.f0);
    assert.ok(Math.abs(c.f0.x - 180) < 1e-6); // rezonans grafiğin ortasında
    assert.ok(c.xl.startsWith('M0 ') && c.xc.startsWith('M0 '));
    assert.equal(reactanceChart(NaN, 0, 0), null);
});

test('signal bars follow phone thresholds', () => {
    assert.equal(signalBars(-45).bars, 5);
    assert.equal(signalBars(-95).bars, 2);
    assert.equal(signalBars(-120).bars, 0);
});

test('color bands put tolerance on the right', () => {
    assert.deepEqual(bandPositions(['d', 'd', 'm', 't']), [112, 142, 172, 262]);
    assert.deepEqual(bandPositions(['d', 'd', 'd', 'm', 't', 'c']), [112, 142, 172, 202, 262, 290]);
});

test('large integers are not written in exponent form', () => {
    assert.equal(num(100000), '100.000');
    assert.equal(num(1234567, 4), '1.234.567');
});

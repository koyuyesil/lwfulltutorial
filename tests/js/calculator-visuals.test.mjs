// Çalıştırma: node --test tests/js/
import { test } from 'node:test';
import assert from 'node:assert/strict';
import { flowDuration, heat, logPos, reactanceChart, signalBars, bandPositions, bandWidth, BAND_AREA, num, stepValue, stepSize, formatSI, parseSI } from '../../resources/js/calculators.js';

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

test('color bands stay on the straight body and put tolerance on the right after a gap', () => {
    for (const layout of [['d', 'd', 'm', 't'], ['d', 'd', 'd', 'm', 't'], ['d', 'd', 'd', 'm', 't', 'c']]) {
        const x = bandPositions(layout);
        const w = bandWidth(layout.length);
        assert.ok(x[0] >= BAND_AREA[0], 'ilk bant kavisli uca taşmamalı');
        assert.ok(x[x.length - 1] + w <= BAND_AREA[1], 'son bant kavisli uca taşmamalı');
        // bantlar üst üste binmez
        x.slice(1).forEach((v, i) => assert.ok(v - x[i] >= w, 'bantlar çakışmamalı'));
        // tolerans öncesi boşluk diğer aralıklardan geniş
        const t = layout.indexOf('t');
        assert.ok(x[t] - x[t - 1] > x[1] - x[0]);
    }
});

test('large integers are not written in exponent form', () => {
    assert.equal(num(100000), '100.000');
    assert.equal(num(1234567, 4), '1.234.567');
});

test('numeric up / down steps by the value magnitude and keeps SI notation', () => {
    assert.equal(stepSize(4700, 1), 100);
    assert.equal(stepSize(1000, -1), 10); // 1000 → 990
    assert.equal(stepValue('4k7', 1), '4,8k');
    assert.equal(stepValue('4k7', -1), '4,6k');
    assert.equal(stepValue('1k', -1), '990');
    assert.equal(stepValue('20m', 1), '21m');
    assert.equal(stepValue('', 1), '1');
    assert.equal(stepValue('0', -1), '-1'); // alt sınır yalnızca verilirse (x-calc.input varsayılanı 0)
    assert.equal(stepValue('0', -1, { min: 0 }), '0');
    assert.equal(stepValue('5', 10), '6');
    assert.equal(parseSI(stepValue('100n', 1)), 1.1e-7);
    // sabit adımlı tam sayı (LED sayısı, sarım)
    assert.equal(stepValue('3', 1, { si: false, step: 1, min: 1, max: 4 }), '4');
    assert.equal(stepValue('4', 1, { si: false, step: 1, min: 1, max: 4 }), '4');
    assert.equal(stepValue('1', -1, { si: false, step: 1, min: 1 }), '1');
    // düz ondalık (birim dönüştürücü)
    assert.equal(stepValue('1,5', 1, { plain: true }), '1,6');
    assert.equal(stepValue('-40', 1, { plain: true }), '-39');
    assert.equal(formatSI(0.0000047), '4,7µ');
});
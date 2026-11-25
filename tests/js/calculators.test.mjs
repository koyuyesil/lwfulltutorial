// Çalıştırma: node --test tests/js/
import { test } from 'node:test';
import assert from 'node:assert/strict';
import { parseSI, si, num, standard, decodeSmd, decodeCapacitor, smdCode, UNIT_CATEGORIES } from '../../resources/js/calculators.js';

test('smd code generation matches decoding', () => {
    for (const [ohms, code] of [[10000, '103'], [4700, '472'], [100000, '104'], [10200, '1022'], [4.7, '4R7'], [0.47, 'R47'], [22100, '2212'], [0, '000']]) {
        assert.equal(smdCode(ohms), code);
        if (ohms >= 10) close(decodeSmd(code).value, ohms);
    }
});

const close = (a, b, eps = 1e-9) => assert.ok(Math.abs(a - b) <= Math.abs(b) * eps + 1e-15, `${a} ≈ ${b}`);

test('parseSI understands prefixes, service notation and turkish comma', () => {
    close(parseSI('4k7'), 4700);
    close(parseSI('2R2'), 2.2);
    close(parseSI('1M5'), 1.5e6);
    close(parseSI('100n'), 1e-7);
    close(parseSI('10µ'), 1e-5);
    close(parseSI('10u'), 1e-5);
    close(parseSI('2,2k'), 2200);
    close(parseSI('4.7 kΩ'), 4700);
    close(parseSI('20mA'), 0.02);
    close(parseSI('1M'), 1e6);
    close(parseSI('1m'), 1e-3);
    close(parseSI('-30'), -30);
    close(parseSI('1e3'), 1000);
    assert.ok(Number.isNaN(parseSI('abc')));
    assert.ok(Number.isNaN(parseSI('')));
});

test('formatting with SI prefixes and turkish decimals', () => {
    assert.equal(si(4700, 'Ω'), '4,7 kΩ');
    assert.equal(si(0.02, 'A'), '20 mA');
    assert.equal(si(1e-7, 'F'), '100 nF');
    assert.equal(si(1000, 'Hz'), '1 kHz');
    assert.equal(si(999.99, 'Ω'), '1 kΩ');
    assert.equal(num(1234567.891, 10), '1.234.567,891');
    assert.equal(num(NaN), '—');
});

test('standard E-series values', () => {
    assert.equal(standard(150, 'E24'), 150);
    assert.equal(standard(155, 'E12'), 150);
    assert.equal(standard(161, 'E24', 'up'), 180);
    assert.equal(standard(9600, 'E12'), 10000);
    assert.equal(standard(4990, 'E96'), 4990);
});

test('smd resistor codes', () => {
    assert.equal(decodeSmd('103').value, 10000);
    assert.equal(decodeSmd('472').value, 4700);
    close(decodeSmd('4R7').value, 4.7);
    assert.equal(decodeSmd('1002').value, 10000);
    assert.equal(decodeSmd('01C').value, 10000);
    close(decodeSmd('68X').value, 49.9);
    close(decodeSmd('R010').value, 0.01);
    assert.equal(decodeSmd('000').value, 0);
    assert.ok(decodeSmd('ZZZ').error);
});

test('capacitor codes', () => {
    close(decodeCapacitor('104').farad, 100e-9);
    close(decodeCapacitor('473K').farad, 47e-9);
    assert.equal(decodeCapacitor('473K').tol, '±%10');
    close(decodeCapacitor('4n7').farad, 4.7e-9);
    close(decodeCapacitor('1R5').farad, 1.5e-12);
    close(decodeCapacitor('109').farad, 1e-12);
    close(decodeCapacitor('475').farad, 4.7e-6);
});

test('unit conversions', () => {
    const conv = (cat, v, from, to) => UNIT_CATEGORIES[cat].fromBase(UNIT_CATEGORIES[cat].toBase(v, from), to);
    close(conv('length', 25.4, 'mm', 'inç'), 1);
    close(conv('temperature', 100, '°C', '°F'), 212);
    close(conv('temperature', 0, '°C', 'K'), 273.15);
    close(conv('data', 1, 'GiB', 'MB'), 1073.741824);
    close(conv('rate', 100, 'Mbit/s', 'MB/s'), 12.5);
    close(conv('energy', 1, 'kWh', 'J'), 3.6e6);
    close(conv('pressure', 1, 'bar', 'psi'), 14.503773773, 1e-8);
    close(conv('charge', 1000, 'mAh', 'Ah'), 1);
    close(conv('capacitance', 100, 'nF', 'µF'), 0.1);
});

<?php

namespace App\Services;

use App\Enums\ComponentType;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Parça ve stok CSV içe / dışa aktarma (Excel uyumlu, UTF-8 BOM).
 * Mevcut parça eşleşmesi: stok kodu (SKU) → tür + parça kodu → ad.
 * Cihaz / muadil ilişkileri çapraz referans JSON'unda taşınır.
 */
class PartCatalog
{
    public const COLUMNS = ['name', 'sku', 'category', 'type', 'code', 'manufacturer', 'cost', 'price', 'stock', 'min_stock', 'supplier', 'compatible', 'notes'];

    public const HEADER_ALIASES = [
        'ad' => 'name', 'parça' => 'name', 'parca' => 'name', 'parça adı' => 'name', 'parca adi' => 'name',
        'stok kodu' => 'sku', 'kategori' => 'category', 'tür' => 'type', 'tur' => 'type', 'kod' => 'code', 'parça kodu' => 'code',
        'üretici' => 'manufacturer', 'uretici' => 'manufacturer', 'alış' => 'cost', 'alis' => 'cost', 'alış fiyatı' => 'cost', 'maliyet' => 'cost',
        'satış' => 'price', 'satis' => 'price', 'satış fiyatı' => 'price', 'fiyat' => 'price', 'stok' => 'stock',
        'min stok' => 'min_stock', 'minimum stok' => 'min_stock', 'tedarikçi' => 'supplier', 'tedarikci' => 'supplier',
        'uyumlu' => 'compatible', 'uyumlu modeller' => 'compatible', 'not' => 'notes', 'notlar' => 'notes',
    ];

    /** @return array{rows: list<array<string, string>>, errors: list<string>} */
    public function parse(string $content): array
    {
        $content = preg_replace('/^\xEF\xBB\xBF/', '', $content);
        $first = strtok($content, "\r\n") ?: '';
        $delimiter = collect([',', ';', "\t"])->sortByDesc(fn ($d) => substr_count($first, $d))->first();

        $stream = fopen('php://temp', 'r+');
        fwrite($stream, $content);
        rewind($stream);
        $header = array_map(function ($h) {
            $h = mb_strtolower(trim((string) $h));

            return in_array($h, self::COLUMNS, true) ? $h : (self::HEADER_ALIASES[$h] ?? $h);
        }, fgetcsv($stream, null, $delimiter, '"', '') ?: []);

        if (! in_array('name', $header, true)) {
            fclose($stream);

            return ['rows' => [], 'errors' => ['CSV başlığında "name" (ya da "Ad" / "Parça") sütunu olmalı.']];
        }

        $rows = [];
        $line = 1;
        while (($cells = fgetcsv($stream, null, $delimiter, '"', '')) !== false) {
            $line++;
            if (! array_filter($cells, fn ($c) => trim((string) $c) !== '')) {
                continue;
            }
            $row = ['_line' => $line];
            foreach ($header as $i => $col) {
                $row[$col] = trim((string) ($cells[$i] ?? ''));
            }
            $rows[] = $row;
        }
        fclose($stream);

        return ['rows' => $rows, 'errors' => []];
    }

    /** @return array{data: array<string, mixed>, errors: list<string>} */
    public function normalize(array $row): array
    {
        $errors = [];
        $text = fn ($k, $max) => ($v = mb_substr(trim((string) ($row[$k] ?? '')), 0, $max)) === '' ? null : $v;
        $money = function ($k) use ($row, &$errors) {
            $v = str_replace([' ', '₺', 'TL'], '', (string) ($row[$k] ?? ''));
            if ($v === '') {
                return 0;
            }
            // 1.250,50 → 1250.50 · 1250,5 → 1250.5
            $v = str_contains($v, ',') ? str_replace(['.', ','], ['', '.'], $v) : $v;
            if (! is_numeric($v) || $v < 0) {
                $errors[] = "Geçersiz fiyat: {$row[$k]}";

                return 0;
            }

            return round((float) $v, 2);
        };
        $int = function ($k, $default) use ($row, &$errors) {
            $v = trim((string) ($row[$k] ?? ''));
            if ($v === '') {
                return $default;
            }
            if (! preg_match('/^-?\d+$/', $v)) {
                $errors[] = "Geçersiz sayı ({$k}): {$v}";

                return $default;
            }

            return (int) $v;
        };

        $typeRaw = mb_strtolower(trim((string) ($row['type'] ?? '')));
        $type = $typeRaw === '' ? null : (ComponentType::tryFrom($typeRaw) ?? collect(ComponentType::cases())->first(fn ($t) => mb_strtolower($t->label()) === $typeRaw || mb_strtolower($t->short()) === $typeRaw));
        if ($typeRaw !== '' && ! $type) {
            $errors[] = "Bilinmeyen tür: {$row['type']}";
        }

        $data = [
            'name' => $text('name', 120) ?? '',
            'sku' => $text('sku', 60),
            'category' => $text('category', 60),
            'type' => $type?->value,
            'code' => $text('code', 120),
            'manufacturer' => $text('manufacturer', 120),
            'cost' => $money('cost'),
            'price' => $money('price'),
            'stock' => $int('stock', 0),
            'min_stock' => max(0, $int('min_stock', 0)),
            'supplier' => $text('supplier', 120),
            'compatible' => $text('compatible', 255),
            'notes' => $text('notes', 2000),
        ];
        if (mb_strlen($data['name']) < 2) {
            $errors[] = 'Parça adı zorunlu (en az 2 karakter).';
        }

        return ['data' => $data, 'errors' => $errors];
    }

    /**
     * new | duplicate (mevcut parça) | repeat (dosyada tekrar) | invalid
     *
     * @return array{items: list<array<string, mixed>>, summary: array<string, int>}
     */
    public function analyze(User $user, array $rows): array
    {
        $existing = $user->parts()->get(['id', 'name', 'sku', 'type', 'code']);
        $low = fn ($s) => mb_strtolower(trim((string) $s));
        $bySku = $existing->filter->sku->keyBy(fn ($p) => $low($p->sku));
        $byCode = $existing->filter->code->keyBy(fn ($p) => ($p->type?->value ?? '').'|'.$low($p->code));
        $byName = $existing->keyBy(fn ($p) => $low($p->name));

        $items = [];
        $seen = [];
        $summary = ['new' => 0, 'duplicate' => 0, 'repeat' => 0, 'invalid' => 0];

        foreach ($rows as $row) {
            ['data' => $d, 'errors' => $errors] = $this->normalize($row);
            $key = $d['sku'] ? 'sku:'.$low($d['sku']) : ($d['code'] ? 'code:'.$d['type'].'|'.$low($d['code']) : 'name:'.$low($d['name']));
            $match = ($d['sku'] ? $bySku[$low($d['sku'])] ?? null : null)
                ?? ($d['code'] ? $byCode[($d['type'] ?? '').'|'.$low($d['code'])] ?? null : null)
                ?? (! $d['sku'] && ! $d['code'] ? $byName[$low($d['name'])] ?? null : null);

            $fatal = mb_strlen($d['name']) < 2;
            $status = match (true) {
                $fatal => 'invalid',
                isset($seen[$key]) => 'repeat',
                $match !== null => 'duplicate',
                default => 'new',
            };
            $seen[$key] = true;
            $summary[$status]++;

            $items[] = [
                'line' => $row['_line'] ?? null, 'status' => $status, 'match_id' => $match?->id, 'data' => $d,
                'errors' => $fatal ? $errors : [], 'warnings' => $fatal ? [] : $errors,
            ];
        }

        return ['items' => $items, 'summary' => $summary];
    }

    /** @return array{created: int, updated: int, skipped: int} */
    public function import(User $user, array $items, string $strategy = 'skip'): array
    {
        $result = ['created' => 0, 'updated' => 0, 'skipped' => 0];

        DB::transaction(function () use ($user, $items, $strategy, &$result) {
            foreach ($items as $item) {
                if (in_array($item['status'], ['invalid', 'repeat'], true)) {
                    $result['skipped']++;

                    continue;
                }
                if ($item['status'] === 'duplicate') {
                    $part = $strategy === 'update' ? $user->parts()->find($item['match_id']) : null;
                    if (! $part) {
                        $result['skipped']++;

                        continue;
                    }
                    // Boş hücreler mevcut bilgiyi silmez
                    $part->update(array_filter($item['data'], fn ($v) => $v !== null && $v !== ''));
                    $result['updated']++;

                    continue;
                }
                $user->parts()->create($item['data']);
                $result['created']++;
            }
        });

        return $result;
    }

    public function exportRows(User $user): \Generator
    {
        yield self::COLUMNS;

        foreach ($user->parts()->orderBy('category')->orderBy('name')->cursor() as $part) {
            yield [
                $part->name, (string) $part->sku, (string) $part->category, (string) $part->type?->value, (string) $part->code,
                (string) $part->manufacturer, number_format((float) $part->cost, 2, '.', ''), number_format((float) $part->price, 2, '.', ''),
                $part->stock, $part->min_stock, (string) $part->supplier, (string) $part->compatible, (string) $part->notes,
            ];
        }
    }
}

<?php

namespace App\Services;

use App\Enums\CompatibilityKind;
use App\Enums\ComponentType;
use App\Models\Part;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Çapraz referans JSON içe / dışa aktarma.
 *
 * {
 *   "format": "koyumavi.cross-reference", "version": 1,
 *   "devices": [{"key": "d1", "brand": "Xiaomi", "model_name": "Redmi Note 13 Pro", "codename": "garnet", …}],
 *   "parts": [{"key": "p1", "type": "soc", "code": "SM7435", "name": "Snapdragon 7s Gen 2", "manufacturer": "Qualcomm",
 *              "specs": {"Üretim süreci": "4 nm"}, "notes": "…",
 *              "devices": [{"device": "d1", "variant": "8/256"}],
 *              "compatible": [{"part": "p2", "kind": "pin", "note": "…"}]}]
 * }
 *
 * Cihazlar yeni cihaz oluşturacak kadar bilgi taşır; aynı cihaz (marka + model ya da kod adı) varsa yeniden eklenmez.
 * Parçalar tür + kod (kod yoksa ad) ile eşleşir; mevcutsa yeniden eklenmez, eksik bağlantıları eklenir.
 */
class CrossReferenceCatalog
{
    public const FORMAT = 'koyumavi.cross-reference';

    public const VERSION = 1;

    public const DEVICE_FIELDS = ['brand', 'model_name', 'model_number', 'codename', 'manufacturer', 'device_type', 'release_year', 'chipset', 'description'];

    /* ================================================================ Dışa aktarma */

    /** @param  list<int>|null  $partIds  yalnızca bu parçalar (null: tüm çapraz referans parçaları) */
    public function export(User $user, ?array $partIds = null): array
    {
        $query = $user->parts()->with(['products', 'compatibleTo', 'compatibleFrom']);
        $parts = ($partIds === null ? $query->reference() : $query->whereIn('id', $partIds))->orderBy('type')->orderBy('code')->orderBy('name')->get();

        // Muadil hedefleri de dosyada olmalı (çapraz referans dışı stok parçası olabilir)
        $missing = $parts->flatMap(fn (Part $p) => $p->compatibles()->pluck('id'))->unique()->diff($parts->pluck('id'));
        if ($missing->isNotEmpty()) {
            $parts = $parts->concat($user->parts()->with(['products', 'compatibleTo', 'compatibleFrom'])->whereIn('id', $missing)->get());
        }
        $inFile = $parts->pluck('id')->flip();

        $devices = $parts->flatMap->products->unique('id')->sortBy(fn ($d) => $d->brand.' '.$d->model_name)->values();

        return [
            'format' => self::FORMAT,
            'version' => self::VERSION,
            'exported_at' => now()->toIso8601String(),
            'devices' => $devices->map(fn (Product $d) => ['key' => 'd'.$d->id] + collect(self::DEVICE_FIELDS)
                ->mapWithKeys(fn ($f) => [$f => $d->{$f} === '' ? null : $d->{$f}])->all())->all(),
            'parts' => $parts->map(fn (Part $p) => array_filter([
                'key' => 'p'.$p->id,
                'type' => $p->type?->value,
                'code' => $p->code,
                'name' => $p->name,
                'manufacturer' => $p->manufacturer,
                'category' => $p->category,
                'sku' => $p->sku,
                'specs' => $p->specs ?: null,
                'notes' => $p->notes,
                'devices' => $p->products->map(fn ($d) => array_filter(['device' => 'd'.$d->id, 'variant' => $d->pivot->variant, 'note' => $d->pivot->note]))->values()->all(),
                // Her çift yalnızca bir kez (küçük kimlikli taraftan) yazılır
                'compatible' => $p->compatibleTo->filter(fn ($o) => isset($inFile[$o->id]))
                    ->map(fn ($o) => array_filter(['part' => 'p'.$o->id, 'kind' => $o->pivot->kind, 'note' => $o->pivot->note]))->values()->all(),
            ], fn ($v) => $v !== null && $v !== '' && $v !== []))->values()->all(),
        ];
    }

    /* ================================================================ İçe aktarma */

    /** @return array{data: array{devices: list<array>, parts: list<array>}, errors: list<string>} */
    public function parse(string $json): array
    {
        $json = preg_replace('/^\xEF\xBB\xBF/', '', $json);
        $data = json_decode($json, true);

        if (! is_array($data)) {
            return ['data' => ['devices' => [], 'parts' => []], 'errors' => ['Dosya geçerli bir JSON değil: '.json_last_error_msg()]];
        }
        if (($data['format'] ?? null) !== self::FORMAT || ! is_array($data['parts'] ?? null)) {
            return ['data' => ['devices' => [], 'parts' => []], 'errors' => ['Bu dosya çapraz referans dışa aktarımı değil ("format": "'.self::FORMAT.'" ve "parts" bekleniyor).']];
        }

        return [
            'data' => [
                'devices' => array_values(array_filter((array) ($data['devices'] ?? []), 'is_array')),
                'parts' => array_values(array_filter($data['parts'], 'is_array')),
            ],
            'errors' => [],
        ];
    }

    /**
     * Önizleme: her cihaz ve parça için durum (new | existing | repeat | invalid) ve bağlantı sayıları.
     *
     * @return array{devices: list<array>, parts: list<array>, summary: array<string, int>}
     */
    public function analyze(User $user, array $data): array
    {
        $products = $user->products()->get(['id', 'brand', 'model_name', 'codename']);
        $parts = $user->parts()->get(['id', 'type', 'code', 'name']);

        /* ---- cihazlar */
        $devices = [];
        $deviceStatus = [];
        $seen = [];
        foreach ($data['devices'] ?? [] as $i => $raw) {
            $d = $this->normalizeDevice($raw);
            $key = (string) ($raw['key'] ?? 'd#'.$i);
            $match = self::matchDevice($products, $d);
            $dedupe = DeviceCatalog::key($d['brand'], $d['model_name']);
            $status = match (true) {
                $d['brand'] === '' || $d['model_name'] === '' => 'invalid',
                isset($seen[$dedupe]) => 'repeat',
                $match !== null => 'existing',
                default => 'new',
            };
            $seen[$dedupe] = $key;
            $deviceStatus[$key] = $status === 'repeat' ? 'existing' : $status;
            $devices[] = ['key' => $key, 'status' => $status, 'match_id' => $match?->id, 'same_as' => $status === 'repeat' ? $seen[$dedupe] : null, 'data' => $d];
        }

        /* ---- parçalar */
        $items = [];
        $seenParts = [];
        $summary = ['parts_new' => 0, 'parts_existing' => 0, 'parts_invalid' => 0, 'devices_new' => 0, 'devices_existing' => 0, 'links' => 0, 'compat' => 0];
        foreach ($data['parts'] ?? [] as $i => $raw) {
            $p = $this->normalizePart($raw);
            $key = (string) ($raw['key'] ?? 'p#'.$i);
            $errors = [];
            if (($raw['type'] ?? '') !== '' && ! $p['type']) {
                $errors[] = 'Bilinmeyen tür: '.$raw['type'];
            }
            if ($p['code'] === null && $p['name'] === null) {
                $errors[] = 'Kod ya da ad gerekli';
            }
            $match = $errors ? null : self::matchPart($parts, $p);
            $dedupe = mb_strtolower(($p['type'] ?? '').'|'.($p['code'] ?? $p['name']));
            $status = match (true) {
                (bool) $errors => 'invalid',
                isset($seenParts[$dedupe]) => 'repeat',
                $match !== null => 'existing',
                default => 'new',
            };
            $seenParts[$dedupe] = true;

            $links = collect((array) ($raw['devices'] ?? []))->filter(fn ($v) => is_array($v))
                ->map(fn ($l) => ['device' => (string) ($l['device'] ?? ''), 'variant' => self::str($l['variant'] ?? null, 60), 'note' => self::str($l['note'] ?? null, 255)])
                ->filter(fn ($l) => isset($deviceStatus[$l['device']]) && $deviceStatus[$l['device']] !== 'invalid')->values()->all();
            $compat = collect((array) ($raw['compatible'] ?? []))->filter(fn ($v) => is_array($v))
                ->map(fn ($c) => ['part' => (string) ($c['part'] ?? ''), 'kind' => CompatibilityKind::tryFrom((string) ($c['kind'] ?? ''))?->value ?? 'direct', 'note' => self::str($c['note'] ?? null, 255)])
                ->filter(fn ($c) => $c['part'] !== '')->values()->all();

            if ($status !== 'invalid') {
                $summary['links'] += count($links);
                $summary['compat'] += count($compat);
            }
            $summary[match ($status) { 'new' => 'parts_new', 'invalid' => 'parts_invalid', default => 'parts_existing' }]++;

            $items[] = [
                'key' => $key, 'status' => $status, 'match_id' => $match?->id, 'errors' => $errors, 'data' => $p,
                'devices' => $links, 'compatible' => $compat,
                'device_labels' => collect($links)->map(fn ($l) => collect($devices)->firstWhere('key', $l['device']))
                    ->filter()->map(fn ($d) => trim($d['data']['brand'].' '.$d['data']['model_name']).($d['status'] === 'new' ? ' (yeni)' : ''))->values()->all(),
            ];
        }

        $summary['devices_new'] = collect($devices)->where('status', 'new')->count();
        $summary['devices_existing'] = collect($devices)->whereIn('status', ['existing', 'repeat'])->count();

        return ['devices' => $devices, 'parts' => $items, 'summary' => $summary];
    }

    /**
     * @param  'skip'|'update'  $strategy  mevcut parçaların bilgileri atlanır ya da güncellenir (bağlantılar her durumda eklenir)
     * @return array{parts_created: int, parts_updated: int, devices_created: int, links: int, compat: int}
     */
    public function import(User $user, array $analysis, string $strategy = 'skip'): array
    {
        $result = ['parts_created' => 0, 'parts_updated' => 0, 'devices_created' => 0, 'links' => 0, 'compat' => 0];

        DB::transaction(function () use ($user, $analysis, $strategy, &$result) {
            $products = $user->products()->get(['id', 'brand', 'model_name', 'codename']);
            $parts = $user->parts()->get(['id', 'type', 'code', 'name']);
            $deviceIds = [];
            $partIds = [];

            foreach ($analysis['devices'] as $d) {
                if ($d['status'] === 'invalid') {
                    continue;
                }
                // Önizlemeden sonra eklenmiş olabilir: yeniden eşleştir
                $product = self::matchDevice($products, $d['data']);
                if (! $product) {
                    $product = $user->products()->create(array_filter($d['data'], fn ($v) => $v !== null && $v !== ''));
                    $products->push($product);
                    $result['devices_created']++;
                }
                $deviceIds[$d['key']] = $product->id;
            }

            foreach ($analysis['parts'] as $item) {
                if ($item['status'] === 'invalid') {
                    continue;
                }
                $data = $item['data'];
                $part = self::matchPart($parts, $data);
                $part = $part ? $user->parts()->find($part->id) : null;

                if (! $part) {
                    $part = $user->parts()->create(array_filter($data, fn ($v) => $v !== null && $v !== '') + [
                        'name' => $data['name'] ?? $data['code'],
                        'category' => self::categoryFor($data['type']),
                        'cost' => 0, 'price' => 0, 'stock' => 0, 'min_stock' => 0,
                    ]);
                    $parts->push($part);
                    $result['parts_created']++;
                } elseif ($strategy === 'update' && $item['status'] === 'existing') {
                    $part->update(array_filter($data, fn ($v) => $v !== null && $v !== '' && $v !== []));
                    $result['parts_updated']++;
                }
                $partIds[$item['key']] = $part->id;

                foreach ($item['devices'] as $link) {
                    $productId = $deviceIds[$link['device']] ?? null;
                    if ($productId && ! $part->products()->wherePivot('product_id', $productId)->wherePivot('variant', $link['variant'])->exists()) {
                        $part->products()->attach($productId, ['variant' => $link['variant'], 'note' => $link['note']]);
                        $result['links']++;
                    }
                }
            }

            // Muadiller: iki taraf da dosyada olmalı
            foreach ($analysis['parts'] as $item) {
                foreach ($item['compatible'] ?? [] as $c) {
                    $a = $partIds[$item['key']] ?? null;
                    $b = $partIds[$c['part']] ?? null;
                    if ($a && $b && $a !== $b) {
                        Part::find($a)->linkCompatible(Part::find($b), CompatibilityKind::from($c['kind']), $c['note']);
                        $result['compat']++;
                    }
                }
            }
        });

        return $result;
    }

    /* ================================================================ Seçim tablosu (metin / Excel) */

    /**
     * parts: seçilen parçalar · devices: seçilen cihazların parçaları (cihaz başına satır grubu)
     *
     * @return list<list<string>>
     */
    public function selectionRows(User $user, string $view, array $ids): array
    {
        $ids = array_values(array_unique(array_map('intval', $ids)));

        if ($view === 'devices') {
            $rows = [['Marka', 'Model', 'Kod adı', 'Model no', 'Parça türü', 'Parça kodu', 'Ad', 'Üretici', 'Varyant', 'Muadil parçalar']];
            $products = $user->products()->whereIn('id', $ids)
                ->with(['parts' => fn ($q) => $q->with(['compatibleTo', 'compatibleFrom'])->orderBy('type')->orderBy('code')])
                ->orderBy('brand')->orderBy('model_name')->get();
            foreach ($products as $p) {
                if ($p->parts->isEmpty()) {
                    $rows[] = [$p->brand, $p->model_name, (string) $p->codename, (string) $p->model_number, '-', '', '', '', '', ''];
                }
                foreach ($p->parts as $c) {
                    $rows[] = [$p->brand, $p->model_name, (string) $p->codename, (string) $p->model_number, (string) $c->type?->label(), (string) $c->code,
                        $c->name, (string) $c->manufacturer, (string) $c->pivot->variant, $c->compatibles()->map->ref()->implode(', ')];
                }
            }

            return $rows;
        }

        $rows = [['Tür', 'Kod', 'Ad', 'Üretici', 'Özellikler', 'Bulunduğu cihazlar', 'Muadil parçalar', 'Not']];
        $parts = $user->parts()->whereIn('id', $ids)->with(['products', 'compatibleTo', 'compatibleFrom'])->orderBy('type')->orderBy('code')->get();
        foreach ($parts as $c) {
            $rows[] = [
                (string) $c->type?->label(), (string) $c->code, $c->name, (string) $c->manufacturer,
                collect((array) $c->specs)->map(fn ($v, $k) => "$k: $v")->implode(', '),
                $c->products->map(fn ($p) => trim($p->brand.' '.$p->model_name).($p->pivot->variant ? ' ('.$p->pivot->variant.')' : ''))->implode(', '),
                $c->compatibles()->map(fn ($o) => $o->ref().' ['.(CompatibilityKind::tryFrom($o->pivot->kind)?->label() ?? $o->pivot->kind).']')->implode(', '),
                (string) $c->notes,
            ];
        }

        return $rows;
    }

    /* ================================================================ yardımcılar */

    protected function normalizeDevice(array $raw): array
    {
        $d = [];
        foreach (self::DEVICE_FIELDS as $f) {
            $d[$f] = self::str($raw[$f] ?? null, $f === 'description' ? 1000 : 120);
        }
        $d['brand'] ??= '';
        $d['model_name'] ??= '';
        $d['manufacturer'] ??= $d['brand'] ?: null;
        $d['release_year'] = is_numeric($d['release_year']) ? (int) $d['release_year'] : null;

        return $d;
    }

    protected function normalizePart(array $raw): array
    {
        $specs = collect(is_array($raw['specs'] ?? null) ? $raw['specs'] : [])
            ->mapWithKeys(fn ($v, $k) => [mb_substr(trim((string) $k), 0, 40) => mb_substr(trim((string) $v), 0, 120)])
            ->filter(fn ($v, $k) => $k !== '' && $v !== '')->all();

        return [
            'type' => ComponentType::tryFrom(strtolower((string) ($raw['type'] ?? '')))?->value,
            'code' => self::str($raw['code'] ?? null, 120),
            'name' => self::str($raw['name'] ?? null, 120),
            'manufacturer' => self::str($raw['manufacturer'] ?? null, 120),
            'category' => self::str($raw['category'] ?? null, 60),
            'sku' => self::str($raw['sku'] ?? null, 60),
            'specs' => $specs ?: null,
            'notes' => self::str($raw['notes'] ?? null, 2000),
        ];
    }

    protected static function str(mixed $value, int $max): ?string
    {
        if (! is_scalar($value)) {
            return null;
        }
        $value = trim((string) $value);

        return $value === '' ? null : mb_substr($value, 0, $max);
    }

    /** Marka + model ya da kod adı ile cihaz bul */
    public static function matchDevice(Collection $products, array $d): ?Product
    {
        $low = fn ($s) => mb_strtolower(trim((string) $s));
        $key = DeviceCatalog::key((string) $d['brand'], (string) $d['model_name']);

        return $products->first(fn ($p) => DeviceCatalog::key($p->brand, $p->model_name) === $key)
            ?? ($d['codename'] ? $products->first(fn ($p) => $p->codename && $low($p->codename) === $low($d['codename'])) : null);
    }

    /** Tür + kod ile (kod yoksa ad ile) parça bul */
    public static function matchPart(Collection $parts, array $p): ?Part
    {
        $low = fn ($s) => mb_strtolower(trim((string) $s));

        if ($p['code']) {
            return $parts->first(fn ($x) => $x->code && $low($x->code) === $low($p['code']) && ($x->type?->value ?? null) === $p['type']);
        }

        return $parts->first(fn ($x) => ! $x->code && $low($x->name) === $low($p['name']));
    }

    public static function categoryFor(?string $type): string
    {
        return match ($type) {
            'display' => 'Ekran', 'battery' => 'Batarya', 'camera' => 'Kamera', 'other', null => 'Diğer',
            default => 'Anakart / entegre',
        };
    }
}

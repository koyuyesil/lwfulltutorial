<?php

namespace App\Services;

use App\Enums\CompatibilityKind;
use App\Enums\ComponentType;
use App\Models\Component;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Çapraz referans CSV içe / dışa aktarma.
 *
 * Sütunlar:
 *  type         soc | ram | storage | mcp | pmic | charging | rf | display | battery | camera | other
 *  code         parça / model kodu (zorunlu)
 *  name, manufacturer, notes
 *  specs        "Anahtar=Değer; Anahtar=Değer"
 *  devices      "Marka > Model [varyant]; kodadı; …"  (Marka > Model ya da kod adı / model adı)
 *  compatible   "tür:kod:uyum; …"  uyum = direct | pin | software (boşsa direct)
 */
class CrossReferenceCatalog
{
    public const COLUMNS = ['type', 'code', 'name', 'manufacturer', 'specs', 'devices', 'compatible', 'notes'];

    /** @return array{rows: list<array<string,string>>, errors: list<string>} */
    public function parse(string $content): array
    {
        $content = preg_replace('/^\xEF\xBB\xBF/', '', $content);
        $first = strtok($content, "\r\n") ?: '';
        $delimiter = collect([',', ';', "\t"])->sortByDesc(fn ($d) => substr_count($first, $d))->first();

        $stream = fopen('php://temp', 'r+');
        fwrite($stream, $content);
        rewind($stream);
        $header = array_map(fn ($h) => strtolower(trim((string) $h)), fgetcsv($stream, null, $delimiter, '"', '') ?: []);

        if (! in_array('type', $header, true) || ! in_array('code', $header, true)) {
            return ['rows' => [], 'errors' => ['CSV başlığında "type" ve "code" sütunları olmalı.']];
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

    /**
     * @return array{components_created: int, components_updated: int, links: int, compat: int, products_created: int, unmatched: list<string>, invalid: list<string>}
     */
    public function import(User $user, array $rows, bool $createDevices = false): array
    {
        $result = ['components_created' => 0, 'components_updated' => 0, 'links' => 0, 'compat' => 0, 'products_created' => 0, 'unmatched' => [], 'invalid' => []];
        $products = $user->products()->get(['id', 'brand', 'model_name', 'codename']);
        $pendingCompat = [];

        DB::transaction(function () use ($user, $rows, $createDevices, $products, &$result, &$pendingCompat) {
            foreach ($rows as $row) {
                $type = ComponentType::tryFrom(strtolower($row['type'] ?? ''));
                $code = mb_substr(trim($row['code'] ?? ''), 0, 120);
                if (! $type || $code === '') {
                    $result['invalid'][] = 'Satır '.($row['_line'] ?? '?').': tür ya da kod geçersiz';

                    continue;
                }

                $component = $user->components()->firstOrNew(['type' => $type->value, 'code' => $code]);
                $component->exists ? $result['components_updated']++ : $result['components_created']++;
                $component->fill(array_filter([
                    'name' => $row['name'] ?? null,
                    'manufacturer' => $row['manufacturer'] ?? null,
                    'notes' => $row['notes'] ?? null,
                    'specs' => self::parseSpecs($row['specs'] ?? '') ?: null,
                ], fn ($v) => $v !== null && $v !== ''))->save();

                foreach (self::splitList($row['devices'] ?? '') as $entry) {
                    [$label, $variant] = self::splitVariant($entry);
                    $product = self::matchProduct($products, $label);

                    if (! $product && $createDevices && str_contains($label, '>')) {
                        [$brand, $model] = array_map('trim', explode('>', $label, 2));
                        $product = $user->products()->create(['brand' => $brand, 'model_name' => $model, 'manufacturer' => $brand]);
                        $products->push($product);
                        $result['products_created']++;
                    }
                    if (! $product) {
                        $result['unmatched'][] = $label;

                        continue;
                    }

                    $exists = $component->products()->wherePivot('product_id', $product->id)->wherePivot('variant', $variant)->exists();
                    if (! $exists) {
                        $component->products()->attach($product->id, ['variant' => $variant]);
                        $result['links']++;
                    }
                }

                foreach (self::splitList($row['compatible'] ?? '') as $entry) {
                    $parts = array_map('trim', explode(':', $entry));
                    if (count($parts) >= 2) {
                        $pendingCompat[] = [$component->id, $parts[0], $parts[1], $parts[2] ?? 'direct'];
                    }
                }
            }

            // Uyumluluklar: hedef bileşen bu dosyada ya da kütüphanede olabilir
            foreach ($pendingCompat as [$id, $type, $code, $kind]) {
                $source = Component::find($id);
                $target = $user->components()->where('type', strtolower($type))->where('code', $code)->first();
                if ($source && $target && ! $target->is($source)) {
                    $source->linkCompatible($target, CompatibilityKind::tryFrom($kind) ?? CompatibilityKind::DIRECT);
                    $result['compat']++;
                } else {
                    $result['unmatched'][] = "{$type}:{$code} (uyumlu bileşen bulunamadı)";
                }
            }
        });

        $result['unmatched'] = array_values(array_unique($result['unmatched']));

        return $result;
    }

    public function exportRows(User $user): \Generator
    {
        yield self::COLUMNS;

        $components = $user->components()->with(['products', 'compatibleTo', 'compatibleFrom'])->orderBy('type')->orderBy('code')->get();
        foreach ($components as $c) {
            yield [
                $c->type->value, $c->code, (string) $c->name, (string) $c->manufacturer,
                collect((array) $c->specs)->map(fn ($v, $k) => "$k=$v")->implode('; '),
                $c->products->map(fn ($p) => $p->brand.' > '.$p->model_name.($p->pivot->variant ? ' ['.$p->pivot->variant.']' : ''))->implode('; '),
                // Her çift yalnızca bir kez (küçük kimlikli taraftan) yazılır
                $c->compatibleTo->map(fn ($o) => $o->type->value.':'.$o->code.':'.$o->pivot->kind)->implode('; '),
                (string) $c->notes,
            ];
        }
    }

    /* ---------------------------------------------------------------- yardımcılar */

    public static function splitList(string $value): array
    {
        return array_values(array_filter(array_map('trim', preg_split('/[;|\n]+/', $value)), fn ($v) => $v !== ''));
    }

    /** "Xiaomi > 13 Pro [12/256]" → ["Xiaomi > 13 Pro", "12/256"] */
    public static function splitVariant(string $entry): array
    {
        if (preg_match('/^(.*?)\s*\[([^\]]+)\]\s*$/u', $entry, $m)) {
            return [trim($m[1]), trim($m[2])];
        }

        return [trim($entry), null];
    }

    public static function parseSpecs(string $value): array
    {
        $specs = [];
        foreach (self::splitList($value) as $pair) {
            [$k, $v] = array_pad(array_map('trim', explode('=', $pair, 2)), 2, '');
            if ($k !== '' && $v !== '') {
                $specs[mb_substr($k, 0, 40)] = mb_substr($v, 0, 120);
            }
        }

        return $specs;
    }

    /** "Marka > Model" ya da kod adı / model adı ile cihaz bul */
    public static function matchProduct(Collection $products, string $label): ?Product
    {
        $low = fn ($s) => mb_strtolower(trim((string) $s));
        if (str_contains($label, '>')) {
            [$brand, $model] = array_map($low, explode('>', $label, 2));

            return $products->first(fn ($p) => $low($p->brand) === $brand && $low($p->model_name) === $model);
        }
        $l = $low($label);

        return $products->first(fn ($p) => $low($p->codename) === $l)
            ?? $products->first(fn ($p) => $low($p->model_name) === $l || $low($p->brand.' '.$p->model_name) === $l);
    }
}

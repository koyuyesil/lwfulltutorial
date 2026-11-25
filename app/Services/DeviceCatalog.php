<?php

namespace App\Services;

use App\Enums\DeviceType;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Cihaz modeli kataloğunun CSV ile içe / dışa aktarılması.
 *
 * Görseller CSV'de "data:image/webp;base64,..." (data URI) ya da ham base64 olarak taşınır.
 * İçe aktarılan raster görseller en fazla 480px'e küçültülüp WebP'ye çevrilir.
 */
class DeviceCatalog
{
    public const COLUMNS = [
        'brand', 'model_name', 'model_number', 'codename', 'manufacturer',
        'device_type', 'release_year', 'chipset', 'description', 'image',
    ];

    /** Türkçe / alternatif başlıklar → sütun */
    public const HEADER_ALIASES = [
        'marka' => 'brand', 'brand' => 'brand',
        'model' => 'model_name', 'model_adi' => 'model_name', 'model adı' => 'model_name', 'model_name' => 'model_name', 'name' => 'model_name',
        'model_no' => 'model_number', 'model no' => 'model_number', 'model_numarasi' => 'model_number', 'model numarası' => 'model_number', 'model_number' => 'model_number', 'sku' => 'model_number',
        'codename' => 'codename', 'kod_adi' => 'codename', 'kod adı' => 'codename', 'kod_adı' => 'codename', 'code_name' => 'codename',
        'uretici' => 'manufacturer', 'üretici' => 'manufacturer', 'manufacturer' => 'manufacturer',
        'tur' => 'device_type', 'tür' => 'device_type', 'tip' => 'device_type', 'device_type' => 'device_type', 'type' => 'device_type',
        'yil' => 'release_year', 'yıl' => 'release_year', 'cikis_yili' => 'release_year', 'çıkış yılı' => 'release_year', 'release_year' => 'release_year', 'year' => 'release_year',
        'yonga' => 'chipset', 'islemci' => 'chipset', 'işlemci' => 'chipset', 'chipset' => 'chipset', 'soc' => 'chipset',
        'aciklama' => 'description', 'açıklama' => 'description', 'description' => 'description', 'not' => 'description',
        'gorsel' => 'image', 'görsel' => 'image', 'resim' => 'image', 'image' => 'image', 'image_base64' => 'image', 'photo' => 'image',
    ];

    public const MAX_ROWS = 5000;

    public const MAX_IMAGE_BYTES = 2 * 1024 * 1024; // çözülmüş görsel üst sınırı

    public const IMAGE_MAX_SIZE = 480; // px

    /* ------------------------------------------------------------ Okuma */

    /**
     * @return array{rows: list<array<string, mixed>>, errors: list<string>, delimiter: string}
     */
    public function parse(string $path): array
    {
        $content = file_get_contents($path);
        if ($content === false || trim($content) === '') {
            return ['rows' => [], 'errors' => ['Dosya boş ya da okunamadı.'], 'delimiter' => ','];
        }

        // BOM ve kodlama
        $content = preg_replace('/^\xEF\xBB\xBF/', '', $content);
        if (! mb_check_encoding($content, 'UTF-8')) {
            $content = mb_convert_encoding($content, 'UTF-8', 'Windows-1254');
        }

        $firstLine = strtok($content, "\r\n") ?: '';
        $delimiter = collect([',', ';', "\t"])->sortByDesc(fn ($d) => substr_count($firstLine, $d))->first();

        $stream = fopen('php://temp', 'r+');
        fwrite($stream, $content);
        rewind($stream);

        $header = fgetcsv($stream, null, $delimiter, '"', '');
        $map = [];
        foreach ((array) $header as $i => $name) {
            $key = mb_strtolower(trim((string) $name));
            if (isset(self::HEADER_ALIASES[$key])) {
                $map[$i] = self::HEADER_ALIASES[$key];
            }
        }

        $errors = [];
        if (! in_array('brand', $map, true) || ! in_array('model_name', $map, true)) {
            fclose($stream);

            return ['rows' => [], 'errors' => ['Başlık satırında en az "brand" (marka) ve "model_name" (model) sütunları olmalıdır.'], 'delimiter' => $delimiter];
        }

        $rows = [];
        $line = 1;
        while (($cells = fgetcsv($stream, null, $delimiter, '"', '')) !== false) {
            $line++;
            if ($cells === [null] || count(array_filter($cells, fn ($c) => trim((string) $c) !== '')) === 0) {
                continue; // boş satır
            }
            if (count($rows) >= self::MAX_ROWS) {
                $errors[] = 'En fazla '.self::MAX_ROWS.' satır içe aktarılabilir; fazlası yok sayıldı.';
                break;
            }

            $row = ['_line' => $line];
            foreach ($map as $i => $column) {
                $row[$column] = isset($cells[$i]) ? trim((string) $cells[$i]) : '';
            }
            $rows[] = $row;
        }
        fclose($stream);

        return ['rows' => $rows, 'errors' => $errors, 'delimiter' => $delimiter];
    }

    /* ------------------------------------------------------------ Doğrulama */

    /**
     * Satırı temizler ve doğrular.
     *
     * @return array{data: array<string, mixed>, errors: list<string>}
     */
    public function normalize(array $row): array
    {
        $errors = [];
        $brand = trim((string) ($row['brand'] ?? ''));
        $model = trim((string) ($row['model_name'] ?? ''));

        if (mb_strlen($brand) < 2) {
            $errors[] = 'Marka eksik';
        }
        if ($model === '') {
            $errors[] = 'Model adı eksik';
        }

        $year = trim((string) ($row['release_year'] ?? ''));
        if ($year !== '' && (! ctype_digit($year) || (int) $year < 1990 || (int) $year > (int) date('Y') + 2)) {
            $errors[] = "Geçersiz yıl: {$year}";
            $year = '';
        }

        $image = null;
        $rawImage = trim((string) ($row['image'] ?? ''));
        if ($rawImage !== '') {
            [$image, $imageError] = $this->normalizeImage($rawImage);
            if ($imageError) {
                $errors[] = $imageError;
            }
        }

        $str = fn ($key, $max) => mb_substr(trim((string) ($row[$key] ?? '')), 0, $max) ?: null;

        return [
            'data' => [
                'brand' => mb_substr($brand, 0, 60),
                'model_name' => mb_substr($model, 0, 80),
                'model_number' => $str('model_number', 80),
                'codename' => $str('codename', 80),
                'manufacturer' => $str('manufacturer', 80) ?? mb_substr($brand, 0, 80),
                'device_type' => DeviceType::fromLoose($row['device_type'] ?? '')->value,
                'release_year' => $year === '' ? null : (int) $year,
                'chipset' => $str('chipset', 80),
                'description' => $str('description', 1000),
                'image' => $image,
            ],
            'errors' => $errors,
        ];
    }

    /**
     * Base64 görseli doğrular; raster görselleri küçültüp WebP data URI'ye çevirir.
     *
     * @return array{0: ?string, 1: ?string} [dataUri, hata]
     */
    public function normalizeImage(string $value): array
    {
        $value = preg_replace('/\s+/', '', $value);
        $declared = null;
        if (preg_match('#^data:([\w/+.-]+);base64,(.+)$#', $value, $m)) {
            $declared = strtolower($m[1]);
            $value = $m[2];
        }

        $binary = base64_decode($value, true);
        if ($binary === false || $binary === '') {
            return [null, 'Görsel geçerli bir base64 değil'];
        }
        if (strlen($binary) > self::MAX_IMAGE_BYTES) {
            return [null, 'Görsel 2 MB sınırını aşıyor'];
        }

        $mime = (new \finfo(FILEINFO_MIME_TYPE))->buffer($binary) ?: $declared;

        // SVG yalnızca <img> içinde gösterilir; yine de betik / olay öznitelikleri reddedilir
        if (in_array($mime, ['image/svg+xml', 'text/xml', 'application/xml', 'text/plain'], true) && str_contains($binary, '<svg')) {
            if (preg_match('/<script|\son\w+\s*=|javascript:|<foreignObject/i', $binary)) {
                return [null, 'SVG görsel güvenli değil (betik içeriyor)'];
            }

            return ['data:image/svg+xml;base64,'.base64_encode($binary), null];
        }

        if (! in_array($mime, ['image/png', 'image/jpeg', 'image/webp', 'image/gif'], true)) {
            return [null, 'Desteklenmeyen görsel türü ('.($mime ?: 'bilinmiyor').')'];
        }

        return [$this->resizeRaster($binary, $mime), null];
    }

    protected function resizeRaster(string $binary, string $mime): string
    {
        if (! function_exists('imagecreatefromstring') || ! ($img = @imagecreatefromstring($binary))) {
            return 'data:'.$mime.';base64,'.base64_encode($binary);
        }

        $w = imagesx($img);
        $h = imagesy($img);
        $scale = min(1, self::IMAGE_MAX_SIZE / max($w, $h));

        if ($scale < 1) {
            $nw = max(1, (int) round($w * $scale));
            $nh = max(1, (int) round($h * $scale));
            $resized = imagecreatetruecolor($nw, $nh);
            imagealphablending($resized, false);
            imagesavealpha($resized, true);
            imagecopyresampled($resized, $img, 0, 0, 0, 0, $nw, $nh, $w, $h);
            imagedestroy($img);
            $img = $resized;
        } else {
            imagesavealpha($img, true);
        }

        ob_start();
        $ok = function_exists('imagewebp') ? imagewebp($img, null, 82) : imagepng($img);
        $out = ob_get_clean();
        imagedestroy($img);

        $outMime = function_exists('imagewebp') ? 'image/webp' : 'image/png';

        return $ok && $out ? 'data:'.$outMime.';base64,'.base64_encode($out) : 'data:'.$mime.';base64,'.base64_encode($binary);
    }

    /* ------------------------------------------------------------ Analiz */

    public static function key(string $brand, string $model): string
    {
        return mb_strtolower(trim($brand)).'|'.mb_strtolower(preg_replace('/\s+/', ' ', trim($model)));
    }

    /**
     * Her satırı sınıflandırır: new | duplicate (mevcut kayıt) | repeat (dosya içinde tekrar) | invalid
     *
     * @return array{items: list<array<string, mixed>>, summary: array<string, int>}
     */
    public function analyze(User $user, array $rows): array
    {
        $existing = $user->products()->get(['id', 'brand', 'model_name', 'model_number']);
        $byKey = $existing->keyBy(fn ($p) => self::key($p->brand, $p->model_name));
        $byNumber = $existing->filter(fn ($p) => $p->model_number)->keyBy(fn ($p) => mb_strtolower($p->model_number));

        $seen = [];
        $items = [];
        $summary = ['new' => 0, 'duplicate' => 0, 'repeat' => 0, 'invalid' => 0];

        foreach ($rows as $row) {
            ['data' => $data, 'errors' => $errors] = $this->normalize($row);
            $key = self::key($data['brand'], $data['model_name']);
            $match = $byKey[$key] ?? ($data['model_number'] ? ($byNumber[mb_strtolower($data['model_number'])] ?? null) : null);

            $status = match (true) {
                $errors && ($data['brand'] === '' || $data['model_name'] === '' || mb_strlen($data['brand']) < 2) => 'invalid',
                isset($seen[$key]) => 'repeat',
                $match !== null => 'duplicate',
                default => 'new',
            };
            $seen[$key] = true;
            $summary[$status]++;

            $items[] = [
                'line' => $row['_line'] ?? null,
                'status' => $status,
                'match_id' => $match?->id,
                'data' => $data,
                'warnings' => $status === 'invalid' ? [] : $errors, // ör. görsel hatası: satır yine de alınır, görselsiz
                'errors' => $status === 'invalid' ? $errors : [],
            ];
        }

        return ['items' => $items, 'summary' => $summary];
    }

    /**
     * @param  'skip'|'update'  $strategy  mevcut kayıtlar atlanır ya da güncellenir
     * @return array{created: int, updated: int, skipped: int}
     */
    public function import(User $user, array $items, string $strategy = 'skip'): array
    {
        $result = ['created' => 0, 'updated' => 0, 'skipped' => 0];

        DB::transaction(function () use ($user, $items, $strategy, &$result) {
            foreach ($items as $item) {
                $data = $item['data'];

                // Önizlemeden sonra aynı kayıt eklenmiş olabilir: yeniden kontrol et
                if ($item['status'] === 'new') {
                    $existing = $user->products()
                        ->whereRaw('LOWER(brand) = ?', [mb_strtolower($data['brand'])])
                        ->whereRaw('LOWER(model_name) = ?', [mb_strtolower($data['model_name'])])
                        ->first();
                    if ($existing) {
                        $item['status'] = 'duplicate';
                        $item['match_id'] = $existing->id;
                    }
                }

                if (in_array($item['status'], ['invalid', 'repeat'], true)) {
                    $result['skipped']++;

                    continue;
                }

                if ($item['status'] === 'duplicate') {
                    if ($strategy !== 'update') {
                        $result['skipped']++;

                        continue;
                    }
                    $product = $user->products()->find($item['match_id']);
                    // Boş hücreler mevcut bilgiyi silmez
                    $product?->update(array_filter($data, fn ($v) => $v !== null && $v !== ''));
                    $result['updated']++;

                    continue;
                }

                $user->products()->create($data);
                $result['created']++;
            }
        });

        return $result;
    }

    /* ------------------------------------------------------------ Dışa aktarma */

    /** CSV satırlarını sırayla üretir (bellek dostu). */
    public function exportRows(User $user): \Generator
    {
        yield self::COLUMNS;

        foreach ($user->products()->orderBy('brand')->orderBy('model_name')->cursor() as $product) {
            yield array_map(fn ($column) => $column === 'device_type'
                ? ($product->device_type ?: DeviceType::PHONE->value)
                : (string) ($product->{$column} ?? ''), self::COLUMNS);
        }
    }

    /** Satırları CSV olarak çıktıya yazar (UTF-8 BOM ile Excel uyumlu). */
    public static function writeCsv(iterable $rows, $handle): void
    {
        fwrite($handle, "\xEF\xBB\xBF");
        foreach ($rows as $row) {
            fputcsv($handle, $row, ',', '"', '');
        }
    }
}

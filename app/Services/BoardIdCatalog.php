<?php

namespace App\Services;

use App\Enums\ChipType;
use App\Enums\DividerType;
use App\Enums\RepairMethod;
use App\Models\BoardId;
use App\Models\User;
use App\Support\Si;
use App\Support\TurkishTextRepair;
use Illuminate\Support\Facades\DB;

/**
 * Board ID kütüphanesinin içe / dışa aktarılması.
 *
 * Desteklenen biçimler (otomatik algılanır):
 *  - CSV: dışa aktarmanın biçimi (COLUMNS).
 *  - Direnç tablosu (TXT): "#device  R1(RSA)  R1(ORG)  R1(READING)  hwc-hwlevel-hwserial  after resistor change  durum  ..."
 *    Sekmelerle hizalanmış servis notu. RSA → R2 (kartta takılı), ORG → R2'nin orijinal değeri, okunan değer atlanır.
 *    Devre tipi belirtilmediğinden dahili pull-up kabul edilir.
 */
class BoardIdCatalog
{
    public const COLUMNS = [
        'codename', 'brand', 'model_name', 'build_name', 'divider_type', 'chip', 'vref',
        'r1', 'r2', 'r1_original', 'r2_original', 'mass_production_hwid', 'pre_production_hwid', 'repair_methods', 'description',
    ];

    public const MAX_ROWS = 5000;

    /** TXT tablosunda cihaz adı gibi görünen ama genel not olan satırlar */
    protected const NOTE_WORDS = ['resistor', 'not', 'note', 'genel'];

    /* ============================================================ Okuma */

    /** @return array{rows: list<array<string, mixed>>, errors: list<string>, warnings: list<string>, format: ?string} */
    public function parse(string $content, ?string $filename = null): array
    {
        $content = preg_replace('/^\xEF\xBB\xBF/', '', $content);
        if (! mb_check_encoding($content, 'UTF-8')) {
            $content = mb_convert_encoding($content, 'UTF-8', 'Windows-1254');
        }

        $warnings = [];
        if (TurkishTextRepair::hasBroken($content)) {
            $content = TurkishTextRepair::fix($content);
            $warnings[] = TurkishTextRepair::hasBroken($content)
                ? 'Dosyada bozuk Türkçe karakterler vardı; bilinen kelimeler onarıldı, bazıları "�" olarak kaldı.'
                : 'Dosyadaki bozuk Türkçe karakterler onarıldı.';
        }

        $firstLine = ltrim(strtok($content, "\r\n") ?: '');

        if (str_starts_with(strtolower($firstLine), '#device')) {
            return ['format' => 'txt'] + $this->parseTable($content, $warnings);
        }
        if (str_contains($firstLine, 'codename') || str_contains($firstLine, 'build_name')) {
            return ['format' => 'csv', 'warnings' => $warnings] + $this->parseCsv($content);
        }

        return ['rows' => [], 'errors' => ['Dosya tanınmadı. Dışa aktarma CSV biçimini ya da "#device R1(RSA) R1(ORG) …" başlıklı direnç tablosunu yükleyin.'], 'warnings' => $warnings, 'format' => null];
    }

    /** @return array{rows: list<array<string, mixed>>, errors: list<string>} */
    protected function parseCsv(string $content): array
    {
        $firstLine = strtok($content, "\r\n") ?: '';
        $delimiter = collect([',', ';', "\t"])->sortByDesc(fn ($d) => substr_count($firstLine, $d))->first();

        $stream = fopen('php://temp', 'r+');
        fwrite($stream, $content);
        rewind($stream);
        $header = array_map(fn ($h) => strtolower(trim((string) $h)), fgetcsv($stream, null, $delimiter, '"', '') ?: []);

        $rows = [];
        $errors = [];
        $line = 1;
        while (($cells = fgetcsv($stream, null, $delimiter, '"', '')) !== false) {
            $line++;
            if ($cells === [null] || ! array_filter($cells, fn ($c) => trim((string) $c) !== '')) {
                continue;
            }
            if (count($rows) >= self::MAX_ROWS) {
                $errors[] = 'En fazla '.self::MAX_ROWS.' satır içe aktarılabilir; fazlası yok sayıldı.';
                break;
            }
            $row = ['_line' => $line];
            foreach ($header as $i => $column) {
                if (in_array($column, self::COLUMNS, true)) {
                    $row[$column] = trim((string) ($cells[$i] ?? ''));
                }
            }
            $rows[] = $row;
        }
        fclose($stream);

        return ['rows' => $rows, 'errors' => $errors];
    }

    /**
     * Sekmeyle hizalanmış direnç tablosu. Hücrelerin sütunu, sekme genişletilmiş konumlarının
     * başlık sütunlarına uzaklığına göre bulunur (boş hücreler yalnızca sekmeden oluşur).
     *
     * @return array{rows: list<array<string, mixed>>, errors: list<string>, warnings: list<string>}
     */
    protected function parseTable(string $content, array $warnings): array
    {
        $lines = preg_split('/\r\n|\r|\n/', $content);
        $headerLine = array_shift($lines);

        // Başlık sütunlarının başlangıç konumları
        $columns = [];
        foreach ($this->tokens($headerLine) as [$pos, $text]) {
            $key = strtolower($text);
            $columns[] = [$pos, match (true) {
                str_starts_with($key, '#device') => 'device',
                str_contains($key, 'rsa') => 'rsa',
                str_contains($key, 'org') => 'org',
                str_contains($key, 'reading') => 'reading',
                str_starts_with($key, 'hwc') => 'hw',
                str_starts_with($key, 'after') => 'after',
                str_starts_with($key, 'durum') || str_starts_with($key, 'status') => 'status',
                default => 'notes',
            }];
        }
        // "after resistor change", "nul resistance" gibi çok kelimeli başlıklar: ilk kelime sütunu belirler
        $columns = collect($columns)->unique(fn ($c) => $c[1])->values()->all();
        $col = fn (string $name) => collect($columns)->firstWhere(1, $name)[0] ?? null;
        $statusPos = $col('status') ?? PHP_INT_MAX;

        $records = [];
        $deviceNotes = [];
        $pendingNote = null;
        $lineNo = 1;

        foreach ($lines as $raw) {
            $lineNo++;
            if (trim($raw) === '') {
                continue;
            }

            [$expanded, $tokens] = $this->tokensWithLine($raw);
            [, $device] = $tokens[0];
            $device = strtolower($device);

            if (in_array($device, self::NOTE_WORDS, true) || ! preg_match('/^[a-z][a-z0-9_]*$/', $device)) {
                $pendingNote = preg_replace('/\s+/u', ' ', trim($raw)); // sonraki cihaza ait genel not

                continue;
            }

            $cells = ['rsa' => null, 'org' => null, 'hw' => null, 'after' => null, 'status' => null];
            $notes = [];
            $modelNo = null;
            $hwPattern = '/^[\w\[\]]+-(mp|p[\d.]+|-?\d+|unknown)-[\w.]+$/i';

            foreach (array_slice($tokens, 1) as $i => [$pos, $text]) {
                $lower = strtolower($text);
                $nearest = collect($columns)->sortBy(fn ($c) => abs($c[0] - $pos))->first()[1] ?? 'notes';

                // Durum hücresi ya da durum sütununu geçen ilk serbest metin: satırın kalanı açıklamadır
                $isStatus = abs($pos - $statusPos) <= 4 && preg_match('/^(ok|err|error|test|\?)$/i', $text);
                $pastStatus = $pos >= $statusPos - 2 && ! preg_match($hwPattern, $text);
                if ($isStatus || $pastStatus) {
                    if ($isStatus) {
                        $cells['status'] = $lower === 'error' ? 'err' : $lower;
                        $restFrom = $pos + mb_strlen($text);
                    } else {
                        $restFrom = $pos;
                    }
                    $notes[] = trim(preg_replace('/\s+/u', ' ', mb_substr($expanded, $restFrom)));

                    break;
                }

                if (preg_match($hwPattern, $text) || str_contains($lower, 'unknown-')) {
                    $slot = in_array($nearest, ['hw', 'after'], true) ? $nearest : 'hw';
                    if ($cells[$slot] !== null) {
                        $slot = $slot === 'hw' ? 'after' : 'hw';
                    }
                    $cells[$slot] = $text;
                } elseif (preg_match('/^(M?\d{4}[A-Z0-9]{3,}|\d{8}[A-Z])$/', $text)) {
                    $modelNo = $text; // ör. M2007J20CG, 22101316C
                } elseif ($this->looksLikeResistor($text) && in_array($nearest, ['rsa', 'org', 'reading'], true)) {
                    if ($nearest !== 'reading' && $cells[$nearest] === null) {
                        $cells[$nearest] = $text; // okunan değer kullanılmaz
                    }
                } elseif (($nearest === 'reading' && $this->looksLikeResistor($text)) || ($text === '?' && in_array($nearest, ['hw', 'after'], true))) {
                    // okunan değer / bilinmeyen hücre: atla
                } else {
                    $notes[] = $text;
                }
            }

            $hasData = array_filter($cells, fn ($v) => $v !== null) !== [];
            $note = trim(implode(' ', $notes));

            if (! $hasData) {
                // Yalnızca not içeren cihaz satırı: o cihazın tüm kayıtlarına eklenir
                $deviceNotes[$device][] = trim(($pendingNote ? $pendingNote.' ' : '').$note);
                $pendingNote = null;

                continue;
            }

            $records[] = [
                '_line' => $lineNo,
                'device' => $device,
                'cells' => $cells,
                'notes' => trim(($pendingNote ? $pendingNote.' ' : '').$note),
                'model_no' => $modelNo,
            ];
            $pendingNote = null;
        }

        // Yalnızca notu olan cihazlar (değer satırı yok) için tek kayıt açılır
        foreach ($deviceNotes as $device => $notes) {
            if (! collect($records)->contains('device', $device)) {
                $records[] = ['_line' => null, 'device' => $device, 'cells' => array_fill_keys(['rsa', 'org', 'hw', 'after', 'status'], null), 'notes' => '', 'model_no' => null];
            }
        }

        $rows = [];
        foreach ($records as $r) {
            $c = $r['cells'];
            $extra = array_filter($deviceNotes[$r['device']] ?? []);
            $description = trim(implode("\n", array_filter([
                $r['notes'],
                ...$extra,
                $c['status'] ? 'Durum: '.$c['status'] : null,
                $r['model_no'] ? 'Model no: '.$r['model_no'] : null,
            ])));

            $rows[] = [
                '_line' => $r['_line'],
                'codename' => $r['device'],
                'brand' => '',
                'model_name' => '',
                'build_name' => $r['device'],
                'divider_type' => DividerType::PULLUP->value,
                'chip' => ChipType::PMIC->value,
                'vref' => '1.8',
                'r1' => '',
                'r2' => $c['rsa'] ?? '',
                'r1_original' => '',
                'r2_original' => $c['org'] ?? '',
                'mass_production_hwid' => $c['hw'] ?? '',
                'pre_production_hwid' => $c['after'] ?? '',
                'repair_methods' => implode('|', $this->inferMethods($description, $c['rsa'] !== null)),
                'description' => $description,
            ];
        }

        return ['rows' => $rows, 'errors' => $rows ? [] : ['Tabloda kayıt bulunamadı.'], 'warnings' => $warnings];
    }

    /** Satırı [konum, metin] parçalarına ayırır; sekmeler 4 sütunluk duraklara genişletilir. */
    protected function tokens(string $line): array
    {
        return $this->tokensWithLine($line)[1];
    }

    /** @return array{0: string, 1: list<array{0: int, 1: string}>} genişletilmiş satır + parçalar */
    protected function tokensWithLine(string $line): array
    {
        $expanded = '';
        $len = 0;
        foreach (mb_str_split($line) as $ch) {
            if ($ch === "\t") {
                $pad = 4 - ($len % 4);
                $expanded .= str_repeat(' ', $pad);
                $len += $pad;
            } else {
                $expanded .= $ch;
                $len++;
            }
        }

        preg_match_all('/\S+/u', $expanded, $m, PREG_OFFSET_CAPTURE);

        // Bayt ofsetini karakter konumuna çevir
        return [$expanded, array_map(fn ($t) => [mb_strlen(substr($expanded, 0, $t[1])), $t[0]], $m[0])];
    }

    protected function looksLikeResistor(string $text): bool
    {
        return (bool) preg_match('/^(open|\??[kK]|\d[\d.,]*\??(-\d[\d.,]*)?[kKmM]?\??~?\?*)$/', $text) && strtolower($text) !== 'ok';
    }

    /**
     * Servis notundaki direnç yazımını ohm'a çevirir. Bu tablolarda küçük "m" mega (MΩ) anlamındadır.
     * open → null (takılı değil), "22-27k" → alt değer, "3.3?k" → 3,3 k, "?k" → null.
     */
    public static function resistor(?string $raw): ?float
    {
        $s = trim((string) $raw);
        if ($s === '' || strtolower($s) === 'open') {
            return null;
        }

        $s = str_replace(['~', '?'], '', $s);
        if (preg_match('/^([\d.,]+)-[\d.,]+([kKmM]?)$/', $s, $m)) {
            $s = $m[1].$m[2]; // aralık: alt değer
        }
        $s = preg_replace('/m$/', 'M', $s);

        $value = Si::parse($s);

        return $value !== null && $value >= 0 && preg_match('/\d/', $s) ? $value : null;
    }

    /** Not ve sütunlardan onarım yöntemi çıkarımı. */
    public function inferMethods(string $text, bool $hasResistor): array
    {
        $t = mb_strtolower($text);
        $methods = [];
        $add = function (RepairMethod $m) use (&$methods) {
            $methods[] = $m->value;
        };

        if ($hasResistor) {
            $add(RepairMethod::RESISTOR);
        }
        if (str_contains($t, 'tmproot') || str_contains($t, 'tmp root')) {
            $add(RepairMethod::TMP_ROOT);
        }
        if (preg_match('/ge(ç|c)ici unlock|tmp ?bl/u', $t)) {
            $add(RepairMethod::TMP_BL_UNLOCK);
        }
        if (str_contains($t, 'patch')) {
            $add(RepairMethod::PATCH);
        }
        if (str_contains($t, 'brom') || str_contains($t, 'burom')) {
            $add(RepairMethod::BROM_BYPASS);
        }
        if (preg_match('/edl.*(auth|bypass)|(auth|bypass).*edl/', $t)) {
            $add(RepairMethod::EDL_AUTH_BYPASS);
        }
        if (str_contains($t, 'metamode') || preg_match('/\bmeta\b/', $t)) {
            $add(RepairMethod::META);
        }
        if (str_contains($t, 'qcn') || str_contains($t, 'diag')) {
            $add(RepairMethod::QC_DIAG);
        }
        if (str_contains($t, 'direct bootloader unlock')) {
            $add(RepairMethod::UNLOCKED);
        }
        if (preg_match('/bl_lock|bl kapal|bl_loc/', $t)) {
            $add(RepairMethod::LOCKED);
        }
        if (preg_match('/rom y(ü|u)kle|fastboot|flash/u', $t)) {
            $add(RepairMethod::FLASH);
        }

        return array_values(array_unique($methods));
    }

    /* ============================================================ Analiz ve kayıt */

    protected function key(array $d): string
    {
        return implode('|', [
            mb_strtolower($d['codename'] ?: $d['model_name']), mb_strtolower($d['build_name']),
            mb_strtolower($d['mass_production_hwid'] ?? ''), mb_strtolower($d['pre_production_hwid'] ?? ''),
            $d['resistances']['R2'] ?? 'nc',
        ]);
    }

    /**
     * new | duplicate | repeat | invalid
     *
     * @return array{items: list<array<string, mixed>>, summary: array<string, int>}
     */
    public function analyze(User $user, array $rows): array
    {
        $existing = $user->boardIds()->with('product:id,codename,model_name')->get()
            ->keyBy(fn (BoardId $b) => $this->key([
                'codename' => (string) $b->product?->codename, 'model_name' => (string) $b->product?->model_name,
                'build_name' => (string) $b->build_name, 'mass_production_hwid' => (string) $b->mass_production_hwid,
                'pre_production_hwid' => (string) $b->pre_production_hwid, 'resistances' => ['R2' => $b->r2()],
            ]));

        $seen = [];
        $items = [];
        $summary = ['new' => 0, 'duplicate' => 0, 'repeat' => 0, 'invalid' => 0];

        foreach ($rows as $row) {
            $errors = [];
            $codename = mb_strtolower(trim((string) ($row['codename'] ?? '')));
            $model = trim((string) ($row['model_name'] ?? ''));
            if ($codename === '' && $model === '') {
                $errors[] = 'Kod adı ya da model yok';
            }

            $type = DividerType::tryFrom((string) ($row['divider_type'] ?? '')) ?? DividerType::PULLUP;
            $chip = ChipType::tryFrom((string) ($row['chip'] ?? '')) ?? ChipType::PMIC;
            $vref = (float) str_replace(',', '.', (string) ($row['vref'] ?? '') ?: '1.8');

            $methods = array_values(array_filter(
                preg_split('/[|,;]+/', (string) ($row['repair_methods'] ?? '')),
                fn ($m) => RepairMethod::tryFrom(trim($m)),
            ));

            $data = [
                'codename' => $codename,
                'brand' => trim((string) ($row['brand'] ?? '')),
                'model_name' => $model,
                'build_name' => mb_substr(trim((string) ($row['build_name'] ?? '')) ?: $codename ?: $model, 0, 80),
                'divider_type' => $type->value,
                'chip' => $chip->value,
                'vref' => $vref >= 0.5 && $vref <= 5 ? $vref : 1.8,
                'resistances' => array_filter([
                    'R1' => self::resistor($row['r1'] ?? null),
                    'R2' => self::resistor($row['r2'] ?? null),
                    'R1_ORG' => self::resistor($row['r1_original'] ?? null),
                    'R2_ORG' => self::resistor($row['r2_original'] ?? null),
                ], fn ($v) => $v !== null) + ['R1' => null, 'R2' => null],
                'mass_production_hwid' => mb_substr(trim((string) ($row['mass_production_hwid'] ?? '')), 0, 80) ?: null,
                'pre_production_hwid' => mb_substr(trim((string) ($row['pre_production_hwid'] ?? '')), 0, 80) ?: null,
                'repair_methods' => array_map('trim', $methods),
                'description' => mb_substr(trim((string) ($row['description'] ?? '')), 0, 2000) ?: null,
            ];

            // Aralık / belirsiz değerlerin yazıldığı hali açıklamada korunur
            foreach (['r2' => 'R2', 'r2_original' => 'R2 orijinal', 'r1' => 'R1', 'r1_original' => 'R1 orijinal'] as $field => $label) {
                $raw = trim((string) ($row[$field] ?? ''));
                if ($raw !== '' && preg_match('/[-?~]/', $raw)) {
                    $data['description'] = trim(($data['description'] ? $data['description']."\n" : '')."{$label} yazılan: {$raw}");
                }
            }

            $key = $this->key($data);
            $match = $existing[$key] ?? null;
            $status = match (true) {
                $errors !== [] => 'invalid',
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
                'errors' => $errors,
            ];
        }

        return ['items' => $items, 'summary' => $summary];
    }

    /** @return array{created: int, updated: int, skipped: int, products: int} */
    public function import(User $user, array $items, string $strategy = 'skip'): array
    {
        $result = ['created' => 0, 'updated' => 0, 'skipped' => 0, 'products' => 0];

        DB::transaction(function () use ($user, $items, $strategy, &$result) {
            $products = [];

            foreach ($items as $item) {
                if (in_array($item['status'], ['invalid', 'repeat'], true)) {
                    $result['skipped']++;

                    continue;
                }

                $data = $item['data'];
                $attributes = collect($data)->only(['build_name', 'divider_type', 'chip', 'vref', 'resistances', 'mass_production_hwid', 'pre_production_hwid', 'repair_methods', 'description'])->all();

                if ($item['status'] === 'duplicate') {
                    $board = $user->boardIds()->find($item['match_id']);
                    if ($board && $strategy === 'update') {
                        $board->update(array_filter($attributes, fn ($v) => $v !== null && $v !== [] && $v !== ''));
                        $result['updated']++;
                    } else {
                        $result['skipped']++;
                    }

                    continue;
                }

                // Model: kod adıyla eşleştir, yoksa oluştur (marka bilinmiyorsa Xiaomi; tablo Xiaomi kod adları içerir)
                $key = $data['codename'] ?: mb_strtolower($data['model_name']);
                $products[$key] ??= ($data['codename']
                    ? $user->products()->whereRaw('LOWER(codename) = ?', [$data['codename']])->first()
                    : $user->products()->whereRaw('LOWER(model_name) = ?', [mb_strtolower($data['model_name'])])->first())
                    ?? tap($user->products()->create([
                        'brand' => $data['brand'] ?: 'Xiaomi',
                        'model_name' => $data['model_name'] ?: $data['codename'],
                        'manufacturer' => $data['brand'] ?: 'Xiaomi',
                        'codename' => $data['codename'] ?: null,
                    ]), function () use (&$result) {
                        $result['products']++;
                    });

                $user->boardIds()->create($attributes + ['product_id' => $products[$key]->id]);
                $result['created']++;
            }
        });

        return $result;
    }

    /* ============================================================ Dışa aktarma */

    public function exportRows(User $user): \Generator
    {
        yield self::COLUMNS;

        foreach ($user->boardIds()->with('product')->orderBy('build_name')->cursor() as $b) {
            $res = (array) $b->resistances;
            $fmt = fn ($v) => $v === null || $v === '' ? '' : rtrim(rtrim(number_format((float) $v, 3, '.', ''), '0'), '.');

            yield [
                (string) $b->product?->codename,
                (string) $b->product?->brand,
                (string) $b->product?->model_name,
                (string) $b->build_name,
                $b->divider_type?->value ?? 'adc',
                $b->chip?->value ?? 'pmic',
                (string) $b->vref,
                $fmt($res['R1'] ?? null),
                $fmt($res['R2'] ?? null),
                $fmt($res['R1_ORG'] ?? null),
                $fmt($res['R2_ORG'] ?? null),
                (string) $b->mass_production_hwid,
                (string) $b->pre_production_hwid,
                implode('|', array_map(fn ($m) => $m->value, $b->methods())),
                (string) $b->description,
            ];
        }
    }

    /** Örnek veri: servis direnç tablosundan dönüştürülmüş 60 kayıt. */
    public static function samplePath(): string
    {
        return database_path('samples/board-ids-ornek.csv');
    }

    /** Örnek dosyadaki kayıt sayısı (çok satırlı açıklamalar tek kayıt sayılır). */
    public static function sampleCount(): int
    {
        $handle = fopen(self::samplePath(), 'r');
        $count = -1; // başlık
        while (fgetcsv($handle, null, ',', '"', '') !== false) {
            $count++;
        }
        fclose($handle);

        return max(0, $count);
    }
}

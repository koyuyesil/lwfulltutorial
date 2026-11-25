<?php

namespace App\Services;

use App\Models\Client;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Müşteri rehberinin CSV ve vCard (2.1 / 3.0 / 4.0) olarak içe / dışa aktarılması.
 *
 * İçe aktarmada biçim (CSV ya da vCard) ve vCard sürümü otomatik algılanır.
 */
class ClientContacts
{
    public const VCARD_VERSIONS = ['2.1', '3.0', '4.0'];

    public const CSV_COLUMNS = ['first_name', 'last_name', 'phone', 'email', 'company', 'address'];

    /** Türkçe, Google Kişiler ve Outlook başlıkları → alan */
    public const CSV_ALIASES = [
        'first_name' => 'first_name', 'ad' => 'first_name', 'adı' => 'first_name', 'isim' => 'first_name', 'first name' => 'first_name', 'given name' => 'first_name',
        'last_name' => 'last_name', 'soyad' => 'last_name', 'soyadı' => 'last_name', 'last name' => 'last_name', 'family name' => 'last_name',
        'name' => 'full_name', 'ad soyad' => 'full_name', 'full name' => 'full_name', 'display name' => 'full_name',
        'phone' => 'phone', 'telefon' => 'phone', 'gsm' => 'phone', 'cep' => 'phone', 'mobile phone' => 'phone', 'phone 1 - value' => 'phone', 'primary phone' => 'phone', 'business phone' => 'phone', 'home phone' => 'phone',
        'email' => 'email', 'e-posta' => 'email', 'eposta' => 'email', 'e-mail' => 'email', 'e-mail address' => 'email', 'e-mail 1 - value' => 'email', 'email address' => 'email',
        'company' => 'company', 'firma' => 'company', 'şirket' => 'company', 'organization' => 'company', 'organization name' => 'company', 'organization 1 - name' => 'company',
        'address' => 'address', 'adres' => 'address', 'address 1 - formatted' => 'address', 'business street' => 'address', 'home street' => 'address',
    ];

    public const MAX_RECORDS = 5000;

    /* ================================================================ Dışa aktarma */

    public function vcard(Client $client, string $version = '3.0'): string
    {
        $version = in_array($version, self::VCARD_VERSIONS, true) ? $version : '3.0';
        $lines = ['BEGIN:VCARD', 'VERSION:'.$version];
        $phone = $this->e164($client->phone);

        if ($version === '2.1') {
            $lines[] = $this->prop21('N', [$client->last_name, $client->first_name, '', '', '']);
            $lines[] = $this->prop21('FN', [$client->full_name]);
            if ($client->company) {
                $lines[] = $this->prop21('ORG', [$client->company]);
            }
            if ($client->phone) {
                $lines[] = 'TEL;CELL;VOICE:'.($phone ?? $client->phone);
            }
            if ($client->email) {
                $lines[] = 'EMAIL;INTERNET:'.$client->email;
            }
            if ($client->address) {
                $lines[] = $this->prop21('ADR;HOME', ['', '', $client->address, '', '', '', '']);
            }
        } else {
            $lines[] = 'N:'.$this->joinEscaped([$client->last_name, $client->first_name, '', '', '']);
            $lines[] = 'FN:'.$this->escape($client->full_name);
            if ($version === '4.0') {
                $lines[] = 'KIND:individual';
            }
            if ($client->company) {
                $lines[] = 'ORG:'.$this->escape($client->company);
            }
            if ($client->phone) {
                $lines[] = $version === '4.0'
                    ? 'TEL;VALUE=uri;TYPE="cell,voice":tel:'.($phone ?? preg_replace('/[^\d+]/', '', $client->phone))
                    : 'TEL;TYPE=CELL,VOICE:'.($phone ?? $client->phone);
            }
            if ($client->email) {
                $lines[] = ($version === '4.0' ? 'EMAIL;TYPE=home:' : 'EMAIL;TYPE=INTERNET:').$client->email;
            }
            if ($client->address) {
                $lines[] = ($version === '4.0' ? 'ADR;TYPE=home:' : 'ADR;TYPE=HOME:').$this->joinEscaped(['', '', $client->address, '', '', '', '']);
            }
            $lines[] = 'UID:'.($version === '4.0' ? 'urn:uuid:' : '').$this->uid($client);
            if ($client->updated_at) {
                $lines[] = 'REV:'.$client->updated_at->copy()->utc()->format('Ymd\THis\Z');
            }
        }

        $lines[] = 'END:VCARD';

        return implode("\r\n", array_map(fn ($l) => $version === '2.1' ? $l : $this->fold($l), $lines))."\r\n";
    }

    /** Tüm müşteriler tek .vcf dosyasında. */
    public function exportVcards(User $user, string $version): \Generator
    {
        foreach ($user->clients()->orderBy('first_name')->orderBy('last_name')->cursor() as $client) {
            yield $this->vcard($client, $version);
        }
    }

    public function exportCsvRows(User $user): \Generator
    {
        yield self::CSV_COLUMNS;

        foreach ($user->clients()->orderBy('first_name')->orderBy('last_name')->cursor() as $client) {
            yield array_map(fn ($c) => (string) ($client->{$c} ?? ''), self::CSV_COLUMNS);
        }
    }

    /** +90 5xx … biçimi; çözümlenemezse null. */
    public function e164(?string $phone): ?string
    {
        $digits = preg_replace('/\D/', '', (string) $phone);

        return match (true) {
            str_starts_with(trim((string) $phone), '+') && strlen($digits) >= 10 => '+'.$digits,
            strlen($digits) === 10 && $digits[0] === '5' => '+90'.$digits,
            strlen($digits) === 11 && $digits[0] === '0' => '+9'.$digits,
            strlen($digits) === 12 && str_starts_with($digits, '90') => '+'.$digits,
            default => null,
        };
    }

    protected function uid(Client $client): string
    {
        // Firma + müşteri için kararlı UUID (aynı kişi her dışa aktarmada aynı UID)
        $h = md5('koyumavi-client-'.$client->user_id.'-'.$client->id);

        return sprintf('%s-%s-4%s-a%s-%s', substr($h, 0, 8), substr($h, 8, 4), substr($h, 13, 3), substr($h, 17, 3), substr($h, 20, 12));
    }

    protected function escape(?string $value): string
    {
        return str_replace(["\\", "\r\n", "\n", ',', ';'], ['\\\\', '\\n', '\\n', '\\,', '\\;'], (string) $value);
    }

    protected function joinEscaped(array $parts): string
    {
        return implode(';', array_map(fn ($p) => $this->escape($p), $parts));
    }

    /** vCard 2.1: ASCII dışı değerler UTF-8 + Quoted-Printable ile yazılır (eski telefon / Outlook uyumu). */
    protected function prop21(string $name, array $parts): string
    {
        $value = implode(';', array_map(fn ($p) => str_replace(';', '\;', (string) $p), $parts));

        if (! preg_match('/[^\x20-\x7E]/', $value)) {
            return $name.':'.$value;
        }

        $qp = str_replace("=\r\n", "=\r\n", quoted_printable_encode($value));

        return $name.';CHARSET=UTF-8;ENCODING=QUOTED-PRINTABLE:'.$qp;
    }

    /** RFC 6350: 75 oktette satır katlama, çok baytlı karakterleri bölmeden. */
    protected function fold(string $line): string
    {
        if (strlen($line) <= 75) {
            return $line;
        }

        $out = [];
        $current = '';
        foreach (mb_str_split($line) as $char) {
            $limit = $out ? 74 : 75; // devam satırlarının başındaki boşluk da sayılır
            if (strlen($current) + strlen($char) > $limit) {
                $out[] = $current;
                $current = '';
            }
            $current .= $char;
        }
        $out[] = $current;

        return implode("\r\n ", $out);
    }

    /* ================================================================ İçe aktarma */

    /** 'vcard' | 'csv' | null */
    public function detectFormat(string $content, ?string $filename = null): ?string
    {
        $ext = strtolower(pathinfo((string) $filename, PATHINFO_EXTENSION));
        $head = ltrim(preg_replace('/^\xEF\xBB\xBF/', '', substr($content, 0, 2048)));

        if (stripos($head, 'BEGIN:VCARD') === 0 || in_array($ext, ['vcf', 'vcard'], true)) {
            return 'vcard';
        }
        if ($ext === 'csv' || $ext === 'txt' || str_contains(strtok($head, "\n") ?: '', ',') || str_contains(strtok($head, "\n") ?: '', ';')) {
            return 'csv';
        }

        return null;
    }

    /**
     * @return array{records: list<array<string, mixed>>, errors: list<string>, format: ?string, versions: array<string, int>}
     */
    public function parse(string $content, ?string $filename = null): array
    {
        $content = preg_replace('/^\xEF\xBB\xBF/', '', $content);
        if (! mb_check_encoding($content, 'UTF-8')) {
            $content = mb_convert_encoding($content, 'UTF-8', 'Windows-1254');
        }

        $format = $this->detectFormat($content, $filename);

        return match ($format) {
            'vcard' => ['format' => 'vcard'] + $this->parseVcards($content),
            'csv' => ['format' => 'csv', 'versions' => []] + $this->parseCsv($content),
            default => ['records' => [], 'errors' => ['Dosya biçimi tanınmadı. CSV ya da vCard (.vcf) yükleyin.'], 'format' => null, 'versions' => []],
        };
    }

    /** @return array{records: list<array<string, mixed>>, errors: list<string>, versions: array<string, int>} */
    public function parseVcards(string $content): array
    {
        $content = str_replace(["\r\n", "\r"], "\n", $content);
        $rawLines = explode("\n", $content);

        // Satır birleştirme: RFC katlaması (boşluk/sekme ile başlayan satır) ve 2.1 QP yumuşak satır sonu (= ile biten)
        $lines = [];
        foreach ($rawLines as $line) {
            $last = $lines ? $lines[count($lines) - 1] : null;
            if ($last !== null && str_ends_with($last, '=') && preg_match('/QUOTED-PRINTABLE/i', strstr($last, ':', true) ?: '')) {
                $lines[count($lines) - 1] = substr($last, 0, -1).$line; // QP yumuşak satır sonu (önce kontrol edilir)
            } elseif ($last !== null && $line !== '' && ($line[0] === ' ' || $line[0] === "\t")) {
                $lines[count($lines) - 1] .= substr($line, 1);
            } else {
                $lines[] = $line;
            }
        }

        $records = [];
        $errors = [];
        $versions = [];
        $card = null;
        $cardNo = 0;

        foreach ($lines as $line) {
            if (trim($line) === '') {
                continue;
            }
            if (preg_match('/^BEGIN:VCARD$/i', trim($line))) {
                $card = [];
                $cardNo++;

                continue;
            }
            if (preg_match('/^END:VCARD$/i', trim($line))) {
                if ($card !== null) {
                    if (count($records) >= self::MAX_RECORDS) {
                        $errors[] = 'En fazla '.self::MAX_RECORDS.' kişi içe aktarılabilir; fazlası yok sayıldı.';
                        break;
                    }
                    $record = $this->vcardToRecord($card);
                    $record['_line'] = $cardNo;
                    $versions[$record['_version']] = ($versions[$record['_version']] ?? 0) + 1;
                    $records[] = $record;
                }
                $card = null;

                continue;
            }
            if ($card === null) {
                continue;
            }

            [$name, $params, $value] = $this->parseLine($line);
            if ($name !== null) {
                $card[] = ['name' => $name, 'params' => $params, 'value' => $value];
            }
        }

        if (! $records && ! $errors) {
            $errors[] = 'Dosyada vCard kaydı bulunamadı.';
        }

        return ['records' => $records, 'errors' => $errors, 'versions' => $versions];
    }

    /** "item1.TEL;TYPE=CELL;CHARSET=UTF-8:değer" → [TEL, [type=>[cell], charset=>..], değer] */
    protected function parseLine(string $line): array
    {
        // Değerin içinde ":" olabilir (ör. tel:+90…), ilk "tırnak dışı" iki noktadan böl
        if (! preg_match('/^((?:[^:"]|"[^"]*")*):(.*)$/s', $line, $m)) {
            return [null, [], ''];
        }

        $segments = preg_split('/;(?=(?:[^"]*"[^"]*")*[^"]*$)/', $m[1]);
        $name = strtoupper(preg_replace('/^[^.]+\./', '', array_shift($segments))); // grup öneki (item1.) atılır
        $params = ['type' => []];

        foreach ($segments as $segment) {
            if (str_contains($segment, '=')) {
                [$key, $val] = explode('=', $segment, 2);
                $key = strtolower(trim($key));
                $val = trim($val, '"');
                if ($key === 'type') {
                    foreach (explode(',', $val) as $t) {
                        $params['type'][] = strtolower(trim($t));
                    }
                } else {
                    $params[$key] = $val;
                }
            } else {
                // vCard 2.1 çıplak parametreler: TEL;CELL;HOME, ADR;QUOTED-PRINTABLE
                $bare = strtolower(trim($segment));
                if (in_array($bare, ['quoted-printable', 'base64', 'b'], true)) {
                    $params['encoding'] = $bare;
                } else {
                    $params['type'][] = $bare;
                }
            }
        }

        $value = $m[2];
        if (strtolower($params['encoding'] ?? '') === 'quoted-printable') {
            $value = quoted_printable_decode($value);
        }
        $charset = strtoupper($params['charset'] ?? 'UTF-8');
        if ($charset !== 'UTF-8' && $charset !== 'UTF8' && function_exists('mb_convert_encoding')) {
            $value = @mb_convert_encoding($value, 'UTF-8', $charset) ?: $value;
        }

        return [$name, $params, $value];
    }

    protected function unescape(string $value): string
    {
        return preg_replace_callback('/\\\\([\\\\,;nN])/', fn ($m) => in_array($m[1], ['n', 'N'], true) ? "\n" : $m[1], $value);
    }

    /** Kaçışlı ayıraca (\;) dikkat ederek bileşenlere ayırır. */
    protected function components(string $value): array
    {
        return array_map(fn ($p) => trim($this->unescape($p)), preg_split('/(?<!\\\\);/', $value));
    }

    protected function vcardToRecord(array $props): array
    {
        $get = fn ($name) => array_values(array_filter($props, fn ($p) => $p['name'] === $name));
        $version = trim($get('VERSION')[0]['value'] ?? '') ?: '3.0';

        $first = $last = '';
        if ($n = $get('N')[0] ?? null) {
            $parts = $this->components($n['value']);
            $last = $parts[0] ?? '';
            $first = trim(($parts[1] ?? '').' '.($parts[2] ?? ''));
        }
        if ($first === '' && $last === '' && ($fn = $get('FN')[0] ?? null)) {
            [$first, $last] = $this->splitName($this->unescape($fn['value']));
        }

        // Telefon: cep / mobil öncelikli; 4.0'da "tel:" URI
        $phones = $get('TEL');
        // Öncelik: cep (WhatsApp ve takip doğrulaması için) > tercih edilen > diğer
        $score = fn ($p) => (array_intersect(['cell', 'mobile'], $p['params']['type']) ? 2 : 0)
            + (in_array('pref', $p['params']['type'], true) || isset($p['params']['pref']) ? 1 : 0);
        usort($phones, fn ($a, $b) => $score($b) <=> $score($a));
        $phone = isset($phones[0]) ? preg_replace('/^tel:/i', '', trim($phones[0]['value'])) : '';

        $email = trim(preg_replace('/^mailto:/i', '', $get('EMAIL')[0]['value'] ?? ''));
        $company = isset($get('ORG')[0]) ? ($this->components($get('ORG')[0]['value'])[0] ?? '') : '';

        $address = '';
        if ($adr = $get('ADR')[0] ?? null) {
            // post-box; extended; street; locality; region; postal-code; country
            $address = implode(', ', array_filter($this->components($adr['value']), fn ($p) => $p !== ''));
        } elseif ($label = $get('LABEL')[0] ?? null) {
            $address = str_replace("\n", ', ', $this->unescape($label['value']));
        }

        return [
            '_version' => $version,
            'first_name' => $first,
            'last_name' => $last,
            'phone' => $phone,
            'email' => $email,
            'company' => $company,
            'address' => $address,
        ];
    }

    protected function splitName(string $full): array
    {
        $parts = preg_split('/\s+/u', trim($full));
        if (count($parts) === 1) {
            return [$parts[0], ''];
        }
        $last = array_pop($parts);

        return [implode(' ', $parts), $last];
    }

    /** @return array{records: list<array<string, mixed>>, errors: list<string>} */
    public function parseCsv(string $content): array
    {
        $firstLine = strtok($content, "\r\n") ?: '';
        $delimiter = collect([',', ';', "\t"])->sortByDesc(fn ($d) => substr_count($firstLine, $d))->first();

        $stream = fopen('php://temp', 'r+');
        fwrite($stream, $content);
        rewind($stream);

        $header = fgetcsv($stream, null, $delimiter, '"', '');
        $map = [];
        foreach ((array) $header as $i => $name) {
            $key = mb_strtolower(trim((string) $name));
            if (isset(self::CSV_ALIASES[$key]) && ! in_array(self::CSV_ALIASES[$key], $map, true)) {
                $map[$i] = self::CSV_ALIASES[$key];
            }
        }

        $hasName = array_intersect(['first_name', 'last_name', 'full_name'], $map) !== [];
        if (! $hasName || ! in_array('phone', $map, true)) {
            fclose($stream);

            return ['records' => [], 'errors' => ['CSV başlığında ad (first_name / ad) ve telefon (phone / telefon) sütunları olmalıdır.']];
        }

        $records = [];
        $errors = [];
        $line = 1;
        while (($cells = fgetcsv($stream, null, $delimiter, '"', '')) !== false) {
            $line++;
            if ($cells === [null] || count(array_filter($cells, fn ($c) => trim((string) $c) !== '')) === 0) {
                continue;
            }
            if (count($records) >= self::MAX_RECORDS) {
                $errors[] = 'En fazla '.self::MAX_RECORDS.' kişi içe aktarılabilir; fazlası yok sayıldı.';
                break;
            }

            $row = ['_line' => $line];
            foreach ($map as $i => $column) {
                $row[$column] = trim((string) ($cells[$i] ?? ''));
            }
            if (($row['full_name'] ?? '') !== '' && ($row['first_name'] ?? '') === '' && ($row['last_name'] ?? '') === '') {
                [$row['first_name'], $row['last_name']] = $this->splitName($row['full_name']);
            }
            unset($row['full_name']);
            $records[] = $row + array_fill_keys(self::CSV_COLUMNS, '');
        }
        fclose($stream);

        return ['records' => $records, 'errors' => $errors];
    }

    /* ================================================================ Analiz ve kayıt */

    public static function phoneKey(?string $phone): ?string
    {
        $digits = preg_replace('/\D/', '', (string) $phone);

        return strlen($digits) >= 10 ? substr($digits, -10) : null;
    }

    /**
     * Her kaydı sınıflandırır: new | duplicate | repeat | invalid
     *
     * @return array{items: list<array<string, mixed>>, summary: array<string, int>}
     */
    public function analyze(User $user, array $records): array
    {
        $existing = $user->clients()->get(['id', 'phone', 'email']);
        $byPhone = $existing->filter(fn ($c) => self::phoneKey($c->phone))->keyBy(fn ($c) => self::phoneKey($c->phone));
        $byEmail = $existing->filter(fn ($c) => $c->email)->keyBy(fn ($c) => mb_strtolower($c->email));

        $seen = [];
        $items = [];
        $summary = ['new' => 0, 'duplicate' => 0, 'repeat' => 0, 'invalid' => 0];

        foreach ($records as $record) {
            $data = [
                'first_name' => mb_substr(trim((string) ($record['first_name'] ?? '')), 0, 60),
                'last_name' => mb_substr(trim((string) ($record['last_name'] ?? '')), 0, 60),
                'phone' => mb_substr(trim((string) ($record['phone'] ?? '')), 0, 25),
                'email' => mb_substr(trim((string) ($record['email'] ?? '')), 0, 120),
                'company' => mb_substr(trim((string) ($record['company'] ?? '')), 0, 120),
                'address' => mb_substr(trim((string) ($record['address'] ?? '')), 0, 255),
            ];

            $errors = [];
            if ($data['first_name'] === '' && $data['last_name'] === '') {
                $errors[] = 'Ad soyad yok';
            }
            $phoneKey = self::phoneKey($data['phone']);
            if (! $phoneKey) {
                $errors[] = 'Geçerli telefon yok (en az 10 hane)';
            }
            $warnings = [];
            if ($data['email'] !== '' && ! filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                $warnings[] = 'Geçersiz e-posta yok sayıldı';
                $data['email'] = '';
            }
            $key = $phoneKey ?? ('email:'.mb_strtolower($data['email']));
            $match = ($phoneKey ? ($byPhone[$phoneKey] ?? null) : null)
                ?? ($data['email'] !== '' ? ($byEmail[mb_strtolower($data['email'])] ?? null) : null);

            $status = match (true) {
                $errors !== [] => 'invalid',
                isset($seen[$key]) => 'repeat',
                $match !== null => 'duplicate',
                default => 'new',
            };
            $seen[$key] = true;
            $summary[$status]++;

            $items[] = [
                'line' => $record['_line'] ?? null,
                'version' => $record['_version'] ?? null,
                'status' => $status,
                'match_id' => $match?->id,
                'data' => $data,
                'errors' => $errors,
                'warnings' => $warnings,
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

                $data = $item['data'];
                $match = $item['status'] === 'duplicate' ? $user->clients()->find($item['match_id']) : null;

                // Önizlemeden sonra eklenmiş olabilir: telefonla yeniden kontrol
                if (! $match && ($key = self::phoneKey($data['phone']))) {
                    $match = $user->clients()->whereRaw(Client::phoneDigitsSql().' like ?', ['%'.$key])->first();
                }

                if ($match) {
                    if ($strategy === 'update') {
                        $match->update(array_filter($data, fn ($v) => $v !== ''));
                        $result['updated']++;
                    } else {
                        $result['skipped']++;
                    }

                    continue;
                }

                // Ad / soyad sütunları boş olamaz (NOT NULL); diğer boş alanlar null kaydedilir
                $user->clients()->create(collect($data)->map(fn ($v, $k) => $v === '' && ! in_array($k, ['first_name', 'last_name'], true) ? null : $v)->all());
                $result['created']++;
            }
        });

        return $result;
    }
}

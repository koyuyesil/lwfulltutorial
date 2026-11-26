<?php

namespace App\Support;

use ZipArchive;

/**
 * Tablo dışa aktarma (harici paket gerektirmez):
 *  - text(): sabit aralıklı metin tablo; sütunlar boşlukla hizalanır ve sekmeyle ayrılır
 *  - xlsx(): Excel çalışma kitabı (kalın başlık, sabitlenmiş başlık satırı, filtre, sütun genişlikleri)
 */
class TableExport
{
    /** @param list<list<scalar|null>> $rows ilk satır başlık */
    public static function text(array $rows): string
    {
        $rows = array_map(fn ($r) => array_map(fn ($v) => str_replace(["\t", "\r", "\n"], ' ', (string) $v), $r), $rows);
        $widths = [];
        foreach ($rows as $row) {
            foreach ($row as $i => $cell) {
                $widths[$i] = max($widths[$i] ?? 0, mb_strwidth($cell));
            }
        }

        $line = fn (array $row) => rtrim(implode("\t", array_map(
            fn ($i) => self::pad($row[$i] ?? '', $widths[$i]),
            array_keys($widths),
        )));

        $out = [$line($rows[0] ?? [])];
        $out[] = rtrim(implode("\t", array_map(fn ($w) => str_repeat('-', $w), $widths)));
        foreach (array_slice($rows, 1) as $row) {
            $out[] = $line($row);
        }

        return "\u{FEFF}".implode("\r\n", $out)."\r\n";
    }

    protected static function pad(string $value, int $width): string
    {
        return $value.str_repeat(' ', max(0, $width - mb_strwidth($value)));
    }

    /** @param list<list<scalar|null>> $rows ilk satır başlık */
    public static function xlsx(array $rows, string $sheet = 'Sayfa1'): string
    {
        $esc = fn ($v) => htmlspecialchars((string) $v, ENT_XML1 | ENT_QUOTES, 'UTF-8');
        $col = function (int $i): string {
            $s = '';
            for ($i++; $i > 0; $i = intdiv($i - 1, 26)) {
                $s = chr(65 + ($i - 1) % 26).$s;
            }

            return $s;
        };

        $cols = max(1, ...array_map('count', $rows ?: [[]]));
        $widths = array_fill(0, $cols, 8);
        foreach ($rows as $row) {
            foreach (array_values($row) as $i => $v) {
                $widths[$i] = min(60, max($widths[$i], mb_strwidth((string) $v) + 2));
            }
        }

        $sheetRows = '';
        foreach (array_values($rows) as $r => $row) {
            $cells = '';
            foreach (array_values($row) as $c => $v) {
                $ref = $col($c).($r + 1);
                $style = $r === 0 ? ' s="1"' : '';
                $cells .= is_int($v) || is_float($v)
                    ? "<c r=\"$ref\"$style><v>$v</v></c>"
                    : "<c r=\"$ref\" t=\"inlineStr\"$style><is><t xml:space=\"preserve\">".$esc($v).'</t></is></c>';
            }
            $sheetRows .= '<row r="'.($r + 1).'">'.$cells.'</row>';
        }
        $last = $col($cols - 1).max(1, count($rows));
        $colXml = implode('', array_map(fn ($w, $i) => '<col min="'.($i + 1).'" max="'.($i + 1).'" width="'.$w.'" customWidth="1"/>', $widths, array_keys($widths)));

        $files = [
            '[Content_Types].xml' => '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"><Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/><Default Extension="xml" ContentType="application/xml"/><Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/><Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/><Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/></Types>',
            '_rels/.rels' => '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/></Relationships>',
            'xl/workbook.xml' => '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"><sheets><sheet name="'.$esc(mb_substr($sheet, 0, 31)).'" sheetId="1" r:id="rId1"/></sheets><definedNames><definedName name="_xlnm._FilterDatabase" localSheetId="0" hidden="1">\''.$esc(mb_substr($sheet, 0, 31)).'\'!$A$1:$'.preg_replace('/\d+/', '', $last).'$'.preg_replace('/\D+/', '', $last).'</definedName></definedNames></workbook>',
            'xl/_rels/workbook.xml.rels' => '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/><Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/></Relationships>',
            'xl/styles.xml' => '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"><fonts count="2"><font><sz val="11"/><name val="Calibri"/></font><font><b/><sz val="11"/><color rgb="FFFFFFFF"/><name val="Calibri"/></font></fonts><fills count="3"><fill><patternFill patternType="none"/></fill><fill><patternFill patternType="gray125"/></fill><fill><patternFill patternType="solid"><fgColor rgb="FF0B1E4D"/><bgColor indexed="64"/></patternFill></fill></fills><borders count="1"><border><left/><right/><top/><bottom/><diagonal/></border></borders><cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs><cellXfs count="2"><xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/><xf numFmtId="0" fontId="1" fillId="2" borderId="0" xfId="0" applyFont="1" applyFill="1"/></cellXfs></styleSheet>',
            'xl/worksheets/sheet1.xml' => '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"><sheetViews><sheetView workbookViewId="0"><pane ySplit="1" topLeftCell="A2" activePane="bottomLeft" state="frozen"/></sheetView></sheetViews><cols>'.$colXml.'</cols><sheetData>'.$sheetRows.'</sheetData><autoFilter ref="A1:'.$last.'"/></worksheet>',
        ];

        $tmp = tempnam(sys_get_temp_dir(), 'xlsx');
        $zip = new ZipArchive;
        $zip->open($tmp, ZipArchive::CREATE | ZipArchive::OVERWRITE);
        foreach ($files as $name => $content) {
            $zip->addFromString($name, $content);
        }
        $zip->close();
        $binary = file_get_contents($tmp);
        @unlink($tmp);

        return $binary;
    }
}

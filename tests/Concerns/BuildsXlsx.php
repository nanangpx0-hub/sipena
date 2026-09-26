<?php

namespace Tests\Concerns;

use ZipArchive;

/**
 * Pembangun berkas .xlsx minimal tanpa dependensi library spreadsheet.
 * Dipakai pengujian unggah ingesti agar lintas mesin tidak butuh Excel/COM.
 */
trait BuildsXlsx
{
    /**
     * Buat berkas xlsx sederhana (satu sheet) dan kembalikan path absolutnya.
     *
     * @param  array<int, array<int, string>>  $rows  Baris data (baris kosong = [] membuat sheetData kosong)
     */
    protected function buildXlsx(string $filename, array $rows, string $sheetName = 'Sheet1'): string
    {
        $dir = sys_get_temp_dir().DIRECTORY_SEPARATOR.'sipena_tests';
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        $path = $dir.DIRECTORY_SEPARATOR.uniqid($filename.'_', true).'.xlsx';

        $xml = function ($value): string {
            return '<c t="inlineStr"><is><t>'.htmlspecialchars($value, ENT_XML1).'</t></is></c>';
        };

        $sheetRows = '';
        foreach ($rows as $row) {
            $cells = implode('', array_map($xml, $row));
            $sheetRows .= '<row>'.$cells.'</row>';
        }

        $zip = new ZipArchive;
        $zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE);
        $zip->addFromString('[Content_Types].xml', '<?xml version="1.0"?><Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"><Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/><Default Extension="xml" ContentType="application/xml"/><Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/><Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/></Types>');
        $zip->addFromString('_rels/.rels', '<?xml version="1.0"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/></Relationships>');
        $zip->addFromString('xl/workbook.xml', '<?xml version="1.0"?><workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"><sheets><sheet name="'.$sheetName.'" sheetId="1" r:id="rId1"/></sheets></workbook>');
        $zip->addFromString('xl/_rels/workbook.xml.rels', '<?xml version="1.0"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/></Relationships>');
        $zip->addFromString('xl/worksheets/sheet1.xml', '<?xml version="1.0"?><worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"><sheetData>'.$sheetRows.'</sheetData></worksheet>');
        $zip->close();

        return $path;
    }
}

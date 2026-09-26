<?php

namespace App\Services;

use App\Models\Publication;
use App\Models\PublicationTable;

class NarrativeEngineService
{
    /**
     * Resolve template tokens seperti {{ total_penduduk }}, {{ luas_wilayah }}, {{ jumlah_desa }}
     * ke dalam teks ulasan bab.
     */
    public function resolveTokens(string $templateText, Publication $publication, int $chapterNumber): string
    {
        $tokens = $this->buildTokenDictionary($publication, $chapterNumber);

        $resolved = $templateText;
        foreach ($tokens as $key => $value) {
            $pattern = '/\{\{\s*' . preg_quote($key, '/') . '\s*\}\}/i';
            $resolved = preg_replace($pattern, (string)$value, $resolved);
        }

        return $resolved;
    }

    /**
     * Membangun kamus token dari database publikasi, kecamatan, dan tabel data
     */
    public function buildTokenDictionary(Publication $publication, int $chapterNumber): array
    {
        $district = $publication->district;
        $tokens = [
            'tahun' => $publication->year,
            'judul_publikasi' => $publication->title,
            'nama_kecamatan' => $district ? $district->name : 'Kabupaten Jember',
            'ibukota_kecamatan' => $district?->capital_city ?? 'Jember',
            'luas_wilayah' => $district?->total_area_sqkm ? number_format($district->total_area_sqkm, 2, ',', '.') . ' km²' : '3.306,68 km²',
            'ketinggian_min' => $district?->altitude_min ?? 0,
            'ketinggian_max' => $district?->altitude_max ?? 500,
        ];

        // Ambil data agregat dari tabel publikasi jika ada
        $tables = PublicationTable::where('publication_id', $publication->id)
            ->where('chapter_number', $chapterNumber)
            ->get();

        foreach ($tables as $table) {
            $tableData = $table->table_data;
            if (is_array($tableData) && isset($tableData['summary'])) {
                foreach ($tableData['summary'] as $k => $v) {
                    $tokens[$k] = is_numeric($v) ? number_format($v, 0, ',', '.') : $v;
                }
            }
        }

        return $tokens;
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\PublicationTable;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TableExportController extends Controller
{
    /**
     * Ekspor satu tabel ingest menjadi berkas CSV (streaming bersih ke php://output).
     *
     * Tidak ada dependensi eksternal: hanya fputcsv native PHP, sehingga memori
     * tetap konstan meski tabel berisi ribuan baris.
     */
    public function exportCsv(int $tableId): StreamedResponse
    {
        $table = PublicationTable::with('publication')->findOrFail($tableId);

        $data = is_array($table->table_data) ? $table->table_data : [];
        $headers = array_values(array_filter((array) ($data['headers'] ?? []), 'is_scalar'));
        $headers = array_map('strval', $headers);

        $rows = is_array($data['data'] ?? null) ? array_values($data['data']) : [];
        $rows = array_values(array_filter($rows, 'is_array'));

        // Data agregasi tanpa baris header tetap diturunkan dari kunci baris pertama.
        if ($headers === [] && $rows !== []) {
            $union = [];
            foreach ($rows as $row) {
                foreach (array_keys($row) as $key) {
                    $union[(string) $key] = true;
                }
            }
            $headers = array_keys($union);
        }

        $filename = $this->downloadName($table);

        return response()->stream(function () use ($headers, $rows) {
            $stream = fopen('php://output', 'w');

            // BOM UTF-8 agar Excel (Windows) membuka karakter non-ASCII tanpa kacau.
            fwrite($stream, "\xEF\xBB\xBF");

            if ($headers !== []) {
                fputcsv($stream, $headers, ',', '"', '\\');
            }

            foreach ($rows as $row) {
                if ($headers === []) {
                    fputcsv($stream, array_values($row), ',', '"', '\\');

                    continue;
                }

                fputcsv($stream, array_map(
                    fn (string $header) => $row[$header] ?? '',
                    $headers,
                ), ',', '"', '\\');
            }

            fclose($stream);
        }, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'X-Accel-Buffering' => 'no',
        ]);
    }

    /**
     * Nama berkas: tabel_{nomor_tabel}_{slug_judul}.csv
     */
    protected function downloadName(PublicationTable $table): string
    {
        $number = preg_replace('/[^A-Za-z0-9._-]+/', '-', (string) $table->table_number) ?: 'tanpa-nomor';
        $titleSlug = Str::slug((string) ($table->title_id ?: 'tabel-'.$table->id));

        return 'tabel_'.$number.'_'.($titleSlug ?: 'tabel').'.csv';
    }
}

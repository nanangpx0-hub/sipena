<?php

namespace App\Jobs;

use App\Models\Publication;
use App\Models\PublicationTable;
use App\Models\User;
use App\Models\WorkflowLog;
use App\Services\TypstCompilerService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class CompilePublicationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $publicationId;

    public ?int $userId;

    public int $timeout = 300;

    /** Batas tampil data tabel dalam PDF agar halaman tetap terkontrol. */
    private const MAX_TABLE_COLUMNS = 8;

    private const MAX_TABLE_ROWS = 40;

    public function __construct(int $publicationId, ?int $userId = null)
    {
        $this->publicationId = $publicationId;
        $this->userId = $userId;
    }

    /**
     * Susun berkas .typ lalu kompilasi ke PDF memakai typst.exe.
     */
    public function handle(TypstCompilerService $typstService): void
    {
        $pub = Publication::with(['district', 'narratives', 'tables'])->findOrFail($this->publicationId);

        $tempDir = storage_path('temp');
        $outputDir = storage_path('output_pdf'.DIRECTORY_SEPARATOR.$pub->year);
        if (! is_dir($tempDir)) {
            mkdir($tempDir, 0755, true);
        }
        if (! is_dir($outputDir)) {
            mkdir($outputDir, 0755, true);
        }

        $safeTitle = Str::slug($pub->title);
        $inputTypPath = "storage/temp/{$safeTitle}_{$pub->id}.typ";
        $outputPdfRelPath = "storage/output_pdf/{$pub->year}/{$safeTitle}.pdf";
        $fullTypPath = base_path(str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $inputTypPath));

        $typstContent = $this->buildTypstDocument($pub, $this->resolveSkdScores($pub));
        File::put($fullTypPath, $typstContent);

        $result = $typstService->compile($inputTypPath, $outputPdfRelPath, 180);

        $userId = $this->userId ?? User::orderBy('id')->value('id');
        $sizeLabel = round($result['file_size_bytes'] / 1024, 2).' KB';

        // Transisi status hanya jika state machine mengizinkan; selain itu tetap DRAF.
        if ($pub->canTransitionTo('FINAL_RELEASED')) {
            $pub->transitionTo('FINAL_RELEASED', $userId, "Kompilasi PDF final berhasil. Ukuran berkas: {$sizeLabel}.");
        } else {
            WorkflowLog::create([
                'publication_id' => $pub->id,
                'from_status' => $pub->status,
                'to_status' => $pub->status,
                'user_id' => $userId,
                'remarks' => "Kompilasi PDF DRAF (status {$pub->status} tidak berubah). Ukuran berkas: {$sizeLabel}.",
            ]);
        }
    }

    /**
     * Skor IKK/IPAK SKD wajib berasal dari perhitungan berkas VKD sah.
     * Tidak ada angka hardcode di sini: bila metrik belum ada, kompilasi dibatalkan.
     */
    protected function resolveSkdScores(Publication $pub): array
    {
        if ($pub->type !== 'SKD') {
            return ['ikk' => '', 'ipak' => ''];
        }

        $cachePath = storage_path('temp'.DIRECTORY_SEPARATOR.'skd_metrics_cache.json');
        $cached = file_exists($cachePath) ? json_decode((string) file_get_contents($cachePath), true) : null;

        if (! is_array($cached) || ($cached['status'] ?? '') !== 'success' || ! isset($cached['ikk_score'])) {
            throw new \RuntimeException(
                'Kompilasi SKD dibatalkan: metrik IKK/IPAK sah belum tersedia. '
                .'Unggah berkas kuesioner VKD melalui modul Analisis SKD terlebih dahulu.'
            );
        }

        return [
            'ikk' => (string) $cached['ikk_score'],
            'ipak' => (string) ($cached['ipak_score'] ?? '-'),
        ];
    }

    /**
     * Perakitan dokumen Typst master (KDA / DDA / SKD).
     */
    protected function buildTypstDocument(Publication $pub, array $skdScores = []): string
    {
        $districtName = $pub->district?->name ?? 'Jember';

        $meta = [
            'title' => $this->typstEscape($pub->title),
            'year' => $this->typstEscape((string) $pub->year),
            'volume' => $this->typstEscape((string) $pub->volume),
            'catalog_no' => $this->typstEscape((string) $pub->catalog_number),
            'pub_no' => $this->typstEscape((string) $pub->publication_number),
            'issn' => $this->typstEscape((string) $pub->issn),
            'district_name' => $this->typstEscape($districtName),
        ];

        if ($pub->type === 'KDA') {
            $content = <<<TYPST
#import "/typst_engine/templates/kda_master.typ": kda-document, narrative-section
#import "/typst_engine/templates/components/divider.typ": chapter-divider
#import "/typst_engine/templates/components/tables.typ": bps-table

#show: doc => kda-document(
  title: "{$meta['title']}",
  year: "{$meta['year']}",
  volume: "{$meta['volume']}",
  catalog_no: "{$meta['catalog_no']}",
  pub_no: "{$meta['pub_no']}",
  issn: "{$meta['issn']}",
  district_name: "{$meta['district_name']}",
  doc,
)

TYPST;
        } elseif ($pub->type === 'DDA') {
            $content = <<<TYPST
#import "/typst_engine/templates/dda_master.typ": dda-document
#import "/typst_engine/templates/components/divider.typ": chapter-divider
#import "/typst_engine/templates/components/tables.typ": bps-table

#show: doc => dda-document(
  title: "{$meta['title']}",
  year: "{$meta['year']}",
  volume: "{$meta['volume']}",
  catalog_no: "{$meta['catalog_no']}",
  pub_no: "{$meta['pub_no']}",
  issn: "{$meta['issn']}",
  doc,
)

TYPST;
        } else {
            $ikk = $this->typstEscape($skdScores['ikk'] ?? '0');
            $ipak = $this->typstEscape($skdScores['ipak'] ?? '0');
            $content = <<<TYPST
#import "/typst_engine/templates/skd_master.typ": skd-document
#import "/typst_engine/templates/components/divider.typ": chapter-divider
#import "/typst_engine/templates/components/tables.typ": bps-table

#show: doc => skd-document(
  title: "{$meta['title']}",
  year: "{$meta['year']}",
  volume: "{$meta['volume']}",
  catalog_no: "{$meta['catalog_no']}",
  pub_no: "{$meta['pub_no']}",
  issn: "{$meta['issn']}",
  ikk_score: "{$ikk}",
  ipak_score: "{$ipak}",
  doc,
)

TYPST;
        }

        $tablesByChapter = $pub->tables->groupBy('chapter_number');

        foreach ($pub->narratives->sortBy('chapter_number') as $nar) {
            $chapter = (int) $nar->chapter_number;
            $tId = $this->typstEscape($nar->title_id);
            $tEn = $this->typstEscape($nar->title_en ?? '');
            $nId = $this->typstEscape($nar->narrative_id ?? '');
            $nEn = $this->typstEscape($nar->narrative_en ?? '');
            $hlLabel = $this->typstEscape($nar->highlight_label ?? '');
            $hlVal = $this->typstEscape($nar->highlight_value ?? '');

            $content .= "\n// Chapter {$chapter} Divider\n";
            $content .= "#chapter-divider(\n"
                ."  chapter_no: {$chapter},\n"
                ."  title_id: \"{$tId}\",\n"
                ."  title_en: \"{$tEn}\",\n"
                ."  highlight_label: \"{$hlLabel}\",\n"
                ."  highlight_val: \"{$hlVal}\",\n"
                .")\n\n";

            $content .= "// Narrative Section\n";
            $content .= "#narrative-section(\n"
                ."  \"{$tId}\",\n"
                ."  \"{$tEn}\",\n"
                ."  \"{$nId}\",\n"
                ."  \"{$nEn}\",\n"
                .")\n\n";

            // Tabel DATA NYATA hasil ingest (bukan placeholder sistem).
            foreach ($tablesByChapter->get($chapter, collect()) as $table) {
                $content .= $this->buildTypsTable($table, $districtName);
            }
        }

        return $content;
    }

    /**
     * Render satu baris PublicationTable menjadi pemanggilan #bps-table().
     * Mengembalikan string kosong bila data tabel tidak layak tampil.
     */
    protected function buildTypsTable(PublicationTable $table, string $districtName): string
    {
        $data = $table->table_data;
        if (! is_array($data)) {
            return '';
        }

        $headers = array_values(array_filter((array) ($data['headers'] ?? []), 'is_scalar'));
        $rows = is_array($data['data'] ?? null) ? $data['data'] : [];

        if ($headers === [] || $rows === []) {
            return '';
        }

        $headers = array_slice($headers, 0, self::MAX_TABLE_COLUMNS);
        $colCount = count($headers);
        $rows = array_slice($rows, 0, self::MAX_TABLE_ROWS);

        $width = rtrim(rtrim(number_format(100 / max($colCount, 1), 2, '.', ''), '0'), '.');
        $colWidths = '('.implode(', ', array_fill(0, $colCount, "{$width}fr")).')';

        $headersId = array_map(fn ($h) => '"'.$this->typstEscape((string) $h).'"', $headers);
        $headersEn = $headersId;

        $cells = [];
        foreach ($rows as $row) {
            if (! is_array($row)) {
                continue;
            }
            $values = array_slice(array_values($row), 0, $colCount);
            while (count($values) < $colCount) {
                $values[] = '-';
            }
            foreach ($values as $value) {
                $cells[] = '"'.$this->typstEscape($this->stringifyCell($value)).'"';
            }
        }

        if ($cells === []) {
            return '';
        }

        $tableNum = $this->typstEscape((string) $table->table_number);
        $titleId = $this->typstEscape($table->title_id ?: 'Tabel '.$table->table_number);
        $titleEn = $this->typstEscape($table->title_en ?? '');
        $source = $this->typstEscape($table->source_agency ?: 'BPS Kabupaten Jember');

        $out = "// Tabel data nyata bab {$table->chapter_number} ({$table->table_number})\n";
        $out .= "#bps-table(\n";
        $out .= "  title_id: \"{$titleId}\",\n";
        $out .= "  title_en: \"{$titleEn}\",\n";
        $out .= "  table_num: \"{$tableNum}\",\n";
        $out .= "  col_widths: {$colWidths},\n";
        $out .= '  headers_id: ('.implode(', ', $headersId)."),\n";
        $out .= '  headers_en: ('.implode(', ', $headersEn)."),\n";
        $out .= '  data_rows: ('.implode(', ', $cells)."),\n";
        $out .= "  source_text: \"{$source}\",\n";
        $out .= ")\n\n";

        return $out;
    }

    /**
     * Normalisasi nilai sel Excel/JSON menjadi teks cetak yang rapi.
     */
    protected function stringifyCell(mixed $value): string
    {
        if ($value === null || $value === '') {
            return '-';
        }
        if (is_bool($value)) {
            return $value ? 'Ya' : 'Tidak';
        }
        if (is_int($value)) {
            return number_format($value, 0, ',', '.');
        }
        if (is_float($value)) {
            $rounded = round($value, 2);

            return number_format($rounded, 2, ',', '.');
        }
        if (is_array($value)) {
            return '-';
        }

        return (string) $value;
    }

    /**
     * Escape string agar aman sebagai literal "..." dalam sintaks Typst.
     * Cukup escape backslash dan kutip ganda; karakter markup (#, $, {}, %)
     * TIDAK perlu di-escape di dalam string dan justru merusak bila di-escape.
     */
    protected function typstEscape(?string $s): string
    {
        $s = (string) ($s ?? '');
        $s = str_replace('\\', '\\\\', $s);
        $s = str_replace('"', '\\"', $s);
        $s = str_replace(["\r\n", "\r", "\n"], ' ', $s);

        return $s;
    }
}

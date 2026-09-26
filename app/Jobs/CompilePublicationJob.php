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
            return ['ikk' => '', 'ipak' => '', 'mutu' => ''];
        }

        // Metrik diisolasi per publikasi agar tahun terbit berbeda tidak tabrakan;
        // berkas cache lama (tanpa sufiks id) tetap dipakai sebagai fallback.
        $cachePath = storage_path('temp'.DIRECTORY_SEPARATOR."skd_metrics_{$pub->id}.json");
        if (! file_exists($cachePath)) {
            $cachePath = storage_path('temp'.DIRECTORY_SEPARATOR.'skd_metrics_cache.json');
        }

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
            'mutu' => (string) ($cached['mutu_pelayanan'] ?? ''),
        ];
    }

    /**
     * Judul Inggris mengikuti pola judul pada publikasi contoh BPS Jember.
     */
    protected function englishTitle(Publication $pub, string $districtName): string
    {
        $year = (int) $pub->year;

        return match ($pub->type) {
            'DDA' => "Jember Regency in Figures {$year}",
            'KDA' => "{$districtName} District in Figures {$year}",
            default => "Analysis of Data Needs Survey Results BPS {$districtName} {$year}",
        };
    }

    /**
     * Label ukuran buku pada kolofon, disesuaikan dengan kertas yang dipakai template.
     */
    protected function bookSizeLabel(Publication $pub): string
    {
        if ($pub->type === 'SKD') {
            return '18,2 cm x 25,7 cm';
        }

        return match ((string) $pub->book_size) {
            'A5' => '14,8 cm x 21 cm',
            'B5' => '17,6 cm x 25 cm',
            default => (string) $pub->book_size,
        };
    }

    /**
     * Daftar OPD yang berkas mentahnya tercatat pada publikasi ini.
     *
     * @return array<int, string>
     */
    protected function contributorNames(Publication $pub): array
    {
        return $pub->rawDataFiles()
            ->distinct()
            ->orderBy('opd_source_name')
            ->pluck('opd_source_name')
            ->values()
            ->all();
    }

    /**
     * Tim penyusun diturunkan dari pengguna yang benar-benar terlibat:
     * pengunggah data mentah dan pelaku riwayat alur kerja publikasi.
     *
     * @return array<int, array{role_id: string, role_en: string, names: array<int, string>}>
     */
    protected function teamEntries(Publication $pub): array
    {
        $ids = $pub->workflowLogs()->pluck('user_id')
            ->merge($pub->rawDataFiles()->pluck('uploaded_by'))
            ->unique()
            ->values();

        if ($ids->isEmpty()) {
            return [];
        }

        $users = User::with('role')->whereIn('id', $ids)->get();

        $labels = [
            'approver' => ['Penanggung Jawab', 'Persons in Charge'],
            'editor' => ['Penyunting', 'Editors'],
            'operator' => ['Pengolah Data dan Penulis Naskah', 'Data Processors and Writers'],
        ];

        $entries = [];
        foreach (array_keys($labels) as $role) {
            $names = $users
                ->filter(fn (User $u) => $u->role?->name === $role)
                ->pluck('name')
                ->sort()
                ->values()
                ->all();

            if ($names === []) {
                continue;
            }

            $entries[] = [
                'role_id' => $labels[$role][0],
                'role_en' => $labels[$role][1],
                'names' => $names,
            ];
        }

        return $entries;
    }

    /**
     * Serialisasi daftar string menjadi tuple Typst, aman untuk elemen tunggal.
     *
     * @param  array<int, string>  $items
     */
    protected function typstArray(array $items): string
    {
        if ($items === []) {
            return '()';
        }

        $inner = implode(', ', $items);

        return count($items) === 1 ? "({$inner},)" : "({$inner})";
    }

    /**
     * Serialisasi daftar string (sudah di-escape) menjadi tuple Typst.
     *
     * @param  array<int, string>  $values
     */
    protected function typstStringTuple(array $values): string
    {
        return $this->typstArray(array_map(fn ($v) => '"'.$v.'"', $values));
    }

    /**
     * Serialisasi entri tim penyusun menjadi tuple dictionary Typst.
     *
     * @param  array<int, array{role_id: string, role_en: string, names: array<int, string>}>  $entries
     */
    protected function typstTeamTuple(array $entries): string
    {
        $parts = [];
        foreach ($entries as $entry) {
            $names = $this->typstStringTuple(array_map(
                fn ($n) => $this->typstEscape($n),
                $entry['names'],
            ));
            $parts[] = '(role_id: "'.$this->typstEscape($entry['role_id'])
                .'", role_en: "'.$this->typstEscape($entry['role_en'])
                .'", names: '.$names.')';
        }

        return $this->typstArray($parts);
    }

    /**
     * Perakitan dokumen Typst master (KDA / DDA / SKD).
     */
    protected function buildTypstDocument(Publication $pub, array $skdScores = []): string
    {
        $districtName = $pub->district?->name ?? 'Jember';

        $meta = [
            'title' => $this->typstEscape($pub->title),
            'title_en' => $this->typstEscape($this->englishTitle($pub, $districtName)),
            'year' => $this->typstEscape((string) $pub->year),
            'volume' => $this->typstEscape((string) $pub->volume),
            'catalog_no' => $this->typstEscape((string) $pub->catalog_number),
            'pub_no' => $this->typstEscape((string) $pub->publication_number),
            'issn' => $this->typstEscape((string) $pub->issn),
            'district_name' => $this->typstEscape($districtName),
            'book_size' => $this->typstEscape($this->bookSizeLabel($pub)),
        ];

        $contributors = $this->typstStringTuple(array_map(
            fn ($n) => $this->typstEscape($n),
            $this->contributorNames($pub),
        ));
        $team = $this->typstTeamTuple($this->teamEntries($pub));
        $hasTables = $pub->tables->isNotEmpty() ? 'true' : 'false';
        // Halaman kosong di akhir dihindari bila publikasi tanpa bab.
        $hasBody = $pub->narratives->isNotEmpty() ? 'true' : 'false';

        $preamble = <<<'TYPST'
#import "/typst_engine/templates/components/divider.typ": chapter-divider
#import "/typst_engine/templates/components/tables.typ": bps-table
#import "/typst_engine/templates/components/narrative.typ": narrative-section

TYPST;

        if ($pub->type === 'KDA') {
            $content = $preamble."#import \"/typst_engine/templates/kda_master.typ\": kda-document\n\n"
                ."#show: doc => kda-document(\n"
                ."  title: \"{$meta['title']}\",\n"
                ."  title_en: \"{$meta['title_en']}\",\n"
                ."  year: \"{$meta['year']}\",\n"
                ."  volume: \"{$meta['volume']}\",\n"
                ."  catalog_no: \"{$meta['catalog_no']}\",\n"
                ."  pub_no: \"{$meta['pub_no']}\",\n"
                ."  issn: \"{$meta['issn']}\",\n"
                ."  district_name: \"{$meta['district_name']}\",\n"
                ."  book_size: \"{$meta['book_size']}\",\n"
                ."  contributors: {$contributors},\n"
                ."  team: {$team},\n"
                ."  has_tables: {$hasTables},\n"
                ."  has_body: {$hasBody},\n"
                ."  doc,\n"
                .")\n\n";
        } elseif ($pub->type === 'DDA') {
            $content = $preamble."#import \"/typst_engine/templates/dda_master.typ\": dda-document\n\n"
                ."#show: doc => dda-document(\n"
                ."  title: \"{$meta['title']}\",\n"
                ."  title_en: \"{$meta['title_en']}\",\n"
                ."  year: \"{$meta['year']}\",\n"
                ."  volume: \"{$meta['volume']}\",\n"
                ."  catalog_no: \"{$meta['catalog_no']}\",\n"
                ."  pub_no: \"{$meta['pub_no']}\",\n"
                ."  issn: \"{$meta['issn']}\",\n"
                ."  book_size: \"{$meta['book_size']}\",\n"
                ."  contributors: {$contributors},\n"
                ."  team: {$team},\n"
                ."  has_tables: {$hasTables},\n"
                ."  has_body: {$hasBody},\n"
                ."  doc,\n"
                .")\n\n";
        } else {
            $ikk = $this->typstEscape($skdScores['ikk'] ?? '0');
            $ipak = $this->typstEscape($skdScores['ipak'] ?? '0');
            $mutu = $this->typstEscape($skdScores['mutu'] ?? '');
            $content = $preamble."#import \"/typst_engine/templates/skd_master.typ\": skd-document\n\n"
                ."#show: doc => skd-document(\n"
                ."  title: \"{$meta['title']}\",\n"
                ."  title_en: \"{$meta['title_en']}\",\n"
                ."  year: \"{$meta['year']}\",\n"
                ."  volume: \"{$meta['volume']}\",\n"
                ."  catalog_no: \"{$meta['catalog_no']}\",\n"
                ."  pub_no: \"{$meta['pub_no']}\",\n"
                ."  issn: \"{$meta['issn']}\",\n"
                ."  book_size: \"{$meta['book_size']}\",\n"
                ."  contributors: {$contributors},\n"
                ."  team: {$team},\n"
                ."  has_tables: {$hasTables},\n"
                ."  ikk_score: \"{$ikk}\",\n"
                ."  ipak_score: \"{$ipak}\",\n"
                ."  ikk_mutu: \"{$mutu}\",\n"
                ."  doc,\n"
                .")\n\n";
        }

        $tablesByChapter = $pub->tables->groupBy('chapter_number');

        // Divider bab pertama tidak memecah halaman: maju sudah dipecah
        // oleh master template, sehingga bab 1 menempel pada halaman
        // bernomor arabik "1" tanpa halaman kosong.
        $firstDivider = true;

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
                .'  first: '.($firstDivider ? 'true' : 'false').",\n"
                .")\n\n";
            $firstDivider = false;

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

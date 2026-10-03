<?php

namespace App\Jobs;

use App\Models\Publication;
use App\Models\PublicationTable;
use App\Models\User;
use App\Models\VisualAsset;
use App\Models\WorkflowLog;
use App\Services\PythonWorkerService;
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
    public function handle(TypstCompilerService $typstService, PythonWorkerService $pythonService): void
    {
        $pub = Publication::with(['district', 'narratives', 'tables', 'visualAssets', 'teamMembers'])->findOrFail($this->publicationId);

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

        $figures = $this->prepareChartFigures($pub, $pythonService);
        $typstContent = $this->buildTypstDocument($pub, $this->resolveSkdScores($pub), $figures);
        File::put($fullTypPath, $typstContent);

        $result = $typstService->compile($inputTypPath, $outputPdfRelPath, 180);

        $userId = $this->userId ?? User::orderBy('id')->value('id');
        $sizeLabel = round($result['file_size_bytes'] / 1024, 2).' KB';

        // Catat durasi terukur ke remarks agar panel performa kompilasi dapat
        // menampilkan ms/halaman tanpa menambah tabel basis data.
        $durationMs = (int) ($result['duration_ms'] ?? 0);
        $pdfPath = base_path(str_replace(['/', '\\\\'], DIRECTORY_SEPARATOR, $outputPdfRelPath));
        $pdfPages = $this->countPdfPages($pdfPath);
        $perfToken = 'DURASI_MS='.$durationMs.' HALAMAN='.($pdfPages ?? '');

        // Transisi status hanya jika state machine mengizinkan; selain itu tetap DRAF.
        if ($pub->canTransitionTo('FINAL_RELEASED')) {
            $pub->transitionTo('FINAL_RELEASED', $userId, "Kompilasi PDF final berhasil. Ukuran berkas: {$sizeLabel}. {$perfToken}.");
        } else {
            WorkflowLog::create([
                'publication_id' => $pub->id,
                'from_status' => $pub->status,
                'to_status' => $pub->status,
                'user_id' => $userId,
                'remarks' => "Kompilasi PDF DRAF (status {$pub->status} tidak berubah). Ukuran berkas: {$sizeLabel}. {$perfToken}.",
            ]);
        }
    }

    /**
     * Hitung jumlah halaman PDF secara terukur (tanpa pustaka PDF): hitung
     * kemunculan objek "/Type /Page" di luar "/Pages". Mengembalikan null
     * bila berkas tidak tersedia atau jumlah halaman tidak dapat dipastikan.
     */
    protected function countPdfPages(?string $path): ?int
    {
        if ($path === null || ! is_file($path)) {
            return null;
        }

        try {
            $content = file_get_contents($path);
            if ($content === false) {
                return null;
            }

            $pageObjects = preg_match_all('/\\/Type\\s*\\/Page[^s]/', $content, $matches);

            return $pageObjects > 0 ? (int) $pageObjects : null;
        } catch (\Throwable $e) {
            return null;
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
     * Tim penyusun diambil dari relasi DB (publication_team_members) bila ada.
     * Fallback ke pengguna yang terlibat di workflow_logs bila belum dikonfigurasi.
     *
     * @return array<int, array{role_id: string, role_en: string, names: array<int, string>}>
     */
    protected function teamEntries(Publication $pub): array
    {
        // Prioritas 1: data yang sudah dikonfigurasi manual di workspace
        if ($pub->teamMembers->isNotEmpty()) {
            return $pub->teamMembers
                ->sortBy('sort_order')
                ->map(fn ($m) => [
                    'role_id' => $m->role_id,
                    'role_en' => $m->role_en,
                    'names' => is_array($m->names) ? $m->names : [],
                ])
                ->values()
                ->all();
        }

        // Fallback: turunkan dari pengguna yang terlibat pada alur kerja publikasi
        $ids = $pub->workflowLogs()->pluck('user_id')
            ->merge($pub->rawDataFiles()->pluck('uploaded_by'))
            ->unique()
            ->values();

        if ($ids->isEmpty()) {
            return [];
        }

        $users = User::with('role')->whereIn('id', $ids)->get();

        $labels = [
            'admin' => ['Penanggung Jawab & Penyunting', 'Persons in Charge and Editors'],
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
     *
     * @param  array{climate?: ?string, pyramid?: ?string}  $figures
     */
    protected function buildTypstDocument(Publication $pub, array $skdScores = [], array $figures = []): string
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

        // Preface dari DB; string kosong berarti template memakai teks default.
        $prefaceId = $this->typstEscape((string) ($pub->preface_id ?? ''));
        $prefaceEn = $this->typstEscape((string) ($pub->preface_en ?? ''));
        $signDate = $this->typstEscape((string) ($pub->sign_date ?? ('Jember, September '.$pub->year)));

        // Daftar singkatan: gunakan custom jika ada, fallback ke default template.
        $abbrArg = '';
        if (! empty($pub->custom_abbreviations) && is_array($pub->custom_abbreviations)) {
            $abbrTerms = [];
            foreach ($pub->custom_abbreviations as $row) {
                if (is_array($row) && count($row) >= 3) {
                    $abbrTerms[] = '("'.$this->typstEscape((string) $row[0])
                        .'", "'.$this->typstEscape((string) $row[1])
                        .'", "'.$this->typstEscape((string) $row[2]).'")';
                }
            }
            if ($abbrTerms !== []) {
                $abbrArg = '  custom_abbr: ('.implode(', ', $abbrTerms)."),\n";
            }
        }

        $preamble = <<<'TYPST'
#import "/typst_engine/templates/components/divider.typ": chapter-divider
#import "/typst_engine/templates/components/tables.typ": bps-table
#import "/typst_engine/templates/components/narrative.typ": narrative-section

TYPST;

        $customCoverAsset = $pub->visualAssets
            ->where('asset_type', 'COVER_CUSTOM')
            ->where('mode', 'MANUAL_OVERRIDE')
            ->last();

        $customCoverArg = '';
        if ($customCoverAsset) {
            $diskPath = base_path(str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $customCoverAsset->file_path));
            if (file_exists($diskPath)) {
                $escapedCoverPath = $this->typstEscape('/'.str_replace('\\', '/', $customCoverAsset->file_path));
                $customCoverArg = "  custom_cover_path: \"{$escapedCoverPath}\",\n";
            }
        }

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
                ."  preface_id: \"{$prefaceId}\",\n"
                ."  preface_en: \"{$prefaceEn}\",\n"
                ."  sign_date: \"{$signDate}\",\n"
                .$abbrArg
                .$customCoverArg
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
                ."  preface_id: \"{$prefaceId}\",\n"
                ."  preface_en: \"{$prefaceEn}\",\n"
                ."  sign_date: \"{$signDate}\",\n"
                .$abbrArg
                .$customCoverArg
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
                .$customCoverArg
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

            // Piramida penduduk tampil di awal Bab 3, sebelum ulasan narasi.
            if ($chapter === 3 && ! empty($figures['pyramid'])) {
                $content .= "// Piramida Penduduk (visualizer Python)\n".$figures['pyramid'];
            }

            $content .= "// Narrative Section\n";
            $content .= "#narrative-section(\n"
                ."  \"{$tId}\",\n"
                ."  \"{$tEn}\",\n"
                ."  \"{$nId}\",\n"
                ."  \"{$nEn}\",\n"
                .")\n\n";

            // Grafik curah hujan menempel di bawah ulasan narasi Bab 1.
            if ($chapter === 1 && ! empty($figures['climate'])) {
                $content .= "// Grafik Curah Hujan (visualizer Python)\n".$figures['climate'];
            }

            // Tabel DATA NYATA hasil ingest (bukan placeholder sistem).
            foreach ($tablesByChapter->get($chapter, collect()) as $table) {
                $content .= $this->buildTypsTable($table, $districtName);
            }
        }

        return $content;
    }

    /**
     * Siapkan grafik vektor Bab 1 (curah hujan) dan Bab 3 (piramida penduduk)
     * untuk KDA/DDA.
     *
     * Grafik HANYA dirender dari data ingesti yang sah (tabel bab terkait);
     * bila sumbernya belum ada, elemen #figure tidak disisipkan sama sekali
     * sehingga tidak pernah ada angka karangan pada dokumen resmi.
     *
     * @return array{climate: ?string, pyramid: ?string}
     */
    protected function prepareChartFigures(Publication $pub, PythonWorkerService $pythonService): array
    {
        if (! in_array($pub->type, ['KDA', 'DDA'], true)) {
            return ['climate' => null, 'pyramid' => null];
        }

        $climate = $this->manualChartFigure($pub, 'CLIMATE_CHART');
        if ($climate === null) {
            $series = $this->climateSeries($pub);
            $climate = $series === null ? null : $this->renderChartFigure(
                $pub,
                $pythonService,
                'CLIMATE_CHART',
                1,
                'climate_chart',
                'Grafik Curah Hujan dan Hari Hujan Bulanan',
                $series,
                fn (string $path, array $data) => $pythonService->renderClimateChart($path, $this->chartDistrictName($pub), (int) $pub->year, $data),
            );
        }

        $pyramid = $this->manualChartFigure($pub, 'POPULATION_PYRAMID');
        if ($pyramid === null) {
            $series = $this->pyramidSeries($pub);
            $pyramid = $series === null ? null : $this->renderChartFigure(
                $pub,
                $pythonService,
                'POPULATION_PYRAMID',
                3,
                'pyramid',
                'Piramida Penduduk Menurut Kelompok Umur',
                $series,
                fn (string $path, array $data) => $pythonService->renderPopulationPyramid($path, $this->chartDistrictName($pub), (int) $pub->year, $data),
            );
        }

        return ['climate' => $climate, 'pyramid' => $pyramid];
    }

    /**
     * Aset grafik hasil unggahan manual (mode MANUAL_OVERRIDE) diprioritaskan.
     */
    protected function manualChartFigure(Publication $pub, string $assetType): ?string
    {
        $asset = $pub->visualAssets()
            ->where('asset_type', $assetType)
            ->where('mode', 'MANUAL_OVERRIDE')
            ->latest('id')
            ->first();

        if (! $asset || ! $asset->file_path) {
            return null;
        }

        if (! file_exists($this->absolutePath($asset->file_path))) {
            return null;
        }

        return $this->figureElement($asset->file_path, $this->chartCaption($assetType));
    }

    /**
     * Render satu grafik via worker Python (bila belum ada / sudah usang)
     * lalu kembalikan elemen Typst #figure yang siap tempel.
     *
     * @param  array<int, mixed>  $series
     * @param  callable(string, array): array  $renderer
     */
    protected function renderChartFigure(
        Publication $pub,
        PythonWorkerService $pythonService,
        string $assetType,
        int $chapter,
        string $slug,
        string $caption,
        array $series,
        callable $renderer,
    ): ?string {
        $relativePath = "storage/custom_assets/{$slug}_{$pub->id}.svg";
        $absolutePath = $this->absolutePath($relativePath);

        $stale = ! file_exists($absolutePath);
        if (! $stale) {
            $latestSource = $pub->tables()
                ->where('chapter_number', $chapter)
                ->max('updated_at');
            $stale = $latestSource !== null && strtotime((string) $latestSource) > filemtime($absolutePath);
        }

        if ($stale) {
            try {
                $renderer($absolutePath, $series);
            } catch (\Throwable $e) {
                $this->logChartFailure($pub, $caption, $e->getMessage());

                return null;
            }

            if (! file_exists($absolutePath)) {
                $this->logChartFailure($pub, $caption, 'worker Python tidak menghasilkan berkas SVG');

                return null;
            }
        }

        $this->rememberVisualAsset($pub, $assetType, $chapter, $relativePath);

        return $this->figureElement($relativePath, $caption);
    }

    /**
     * Susun elemen Typst #figure dengan path relatif terhadap --root proyek
     * (forward slash, tanpa spasi/backslash Windows).
     */
    protected function figureElement(string $relativePath, string $caption): string
    {
        $path = '/'.ltrim(str_replace('\\', '/', $relativePath), '/');

        return '#figure(image("'.$path.'", width: 90%), caption: ['.$caption.'])'."\n\n";
    }

    protected function chartCaption(string $assetType): string
    {
        return $assetType === 'POPULATION_PYRAMID'
            ? 'Piramida Penduduk Menurut Kelompok Umur'
            : 'Grafik Curah Hujan dan Hari Hujan Bulanan';
    }

    protected function chartDistrictName(Publication $pub): string
    {
        return $pub->district?->name ?? 'Kabupaten Jember';
    }

    /**
     * Catat aset grafik hasil render otomatis agar dapat diaudit pada modul aset visual.
     */
    protected function rememberVisualAsset(Publication $pub, string $assetType, int $chapter, string $relativePath): void
    {
        $existing = $pub->visualAssets()
            ->where('asset_type', $assetType)
            ->where('mode', 'AUTO_GENERATED')
            ->first();

        $payload = [
            'chapter_number' => $chapter,
            'file_path' => $relativePath,
        ];

        if ($existing) {
            $existing->update($payload);

            return;
        }

        VisualAsset::create([
            'publication_id' => $pub->id,
            'asset_type' => $assetType,
            'mode' => 'AUTO_GENERATED',
            ...$payload,
        ]);
    }

    protected function logChartFailure(Publication $pub, string $caption, string $reason): void
    {
        WorkflowLog::create([
            'publication_id' => $pub->id,
            'from_status' => $pub->status,
            'to_status' => $pub->status,
            'user_id' => $this->userId ?? User::orderBy('id')->value('id'),
            'remarks' => "Render grafik dibatalkan ({$caption}): {$reason}. Elemen grafik tidak disisipkan.",
        ]);
    }

    protected function absolutePath(string $relativePath): string
    {
        return base_path(str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $relativePath));
    }

    /**
     * Ekstrak deret curah hujan & hari hujan (12 bulan) dari tabel ingesti Bab 1.
     *
     * @return array{rainfall: array<int, int|float>, rain_days: array<int, int|float>}|null
     */
    protected function climateSeries(Publication $pub): ?array
    {
        foreach ($pub->tables->where('chapter_number', 1) as $table) {
            [$headers, $rows] = $this->tableGrid($table);
            if ($headers === [] || $rows === []) {
                continue;
            }

            $monthColumn = $this->detectColumnBy($headers, $rows, fn (mixed $value) => $this->monthIndex($value) !== null, 10);
            if ($monthColumn === null) {
                continue;
            }

            $numericColumns = array_values(array_filter(
                $headers,
                fn (string $header) => $this->countMatching($headers, $rows, $header, fn (mixed $value) => $this->numericValue($value) !== null) >= 10
            ));
            if (count($numericColumns) < 2) {
                continue;
            }

            $rainfallColumn = $this->preferHeader($numericColumns, '/curah|rainfall|precip|presipitasi|\bmm\b/i');
            $rainDaysColumn = null;
            foreach ($numericColumns as $header) {
                if ($header !== $rainfallColumn && preg_match('/hari|day/i', $header)) {
                    $rainDaysColumn = $header;
                    break;
                }
            }

            if ($rainfallColumn === null || $rainDaysColumn === null) {
                // Fallback ketat: tepat dua kolom numerik dan kolom kedua seluruhnya
                // <= 31 (plausibel jumlah hari) supaya tidak salah menafsirkan kolom.
                if (count($numericColumns) !== 2) {
                    continue;
                }
                [$first, $second] = $numericColumns;
                if ($this->allValuesAtMost($rows, $second, 31) && ! $this->allValuesAtMost($rows, $first, 31)) {
                    $rainfallColumn = $first;
                    $rainDaysColumn = $second;
                } elseif ($this->allValuesAtMost($rows, $first, 31) && ! $this->allValuesAtMost($rows, $second, 31)) {
                    $rainfallColumn = $second;
                    $rainDaysColumn = $first;
                } else {
                    continue;
                }
            }

            $rainfall = array_fill(0, 12, null);
            $rainDays = array_fill(0, 12, null);

            foreach ($rows as $row) {
                $monthIndex = $this->monthIndex($row[$monthColumn] ?? null);
                if ($monthIndex === null) {
                    continue;
                }
                $mm = $this->numericValue($row[$rainfallColumn] ?? null);
                $days = $this->numericValue($row[$rainDaysColumn] ?? null);
                if ($mm === null || $days === null || $mm < 0 || $days < 0) {
                    continue;
                }
                $rainfall[$monthIndex] = $mm;
                $rainDays[$monthIndex] = $days;
            }

            if (in_array(null, $rainfall, true) || in_array(null, $rainDays, true)) {
                continue;
            }

            return [
                'rainfall' => array_map(fn ($v) => $v + 0, $rainfall),
                'rain_days' => array_map(fn ($v) => (int) round((float) $v), $rainDays),
            ];
        }

        return null;
    }

    /**
     * Ekstrak deret piramida penduduk 16 kelompok umur dari tabel ingesti Bab 3.
     *
     * @return array{males: array<int, int|float>, females: array<int, int|float>}|null
     */
    protected function pyramidSeries(Publication $pub): ?array
    {
        foreach ($pub->tables->where('chapter_number', 3) as $table) {
            [$headers, $rows] = $this->tableGrid($table);
            if ($headers === [] || $rows === []) {
                continue;
            }

            $maleColumn = $this->preferHeader($headers, '/laki|male/i');
            $femaleColumn = $this->preferHeader($headers, '/perempuan|female/i');
            $ageColumn = $this->detectColumnBy($headers, $rows, fn (mixed $value) => $this->ageGroupIndex($value) !== null, 16);

            if ($ageColumn === null) {
                continue;
            }

            if ($maleColumn === null || $femaleColumn === null || $maleColumn === $femaleColumn) {
                // Fallback: tepat dua kolom numerik selain kolom umur.
                $numericColumns = array_values(array_filter(
                    $headers,
                    fn (string $header) => $header !== $ageColumn
                        && $this->countMatching($headers, $rows, $header, fn (mixed $value) => $this->numericValue($value) !== null) >= 16
                ));
                if (count($numericColumns) !== 2) {
                    continue;
                }
                [$maleColumn, $femaleColumn] = $numericColumns;
            }

            $males = array_fill(0, 16, null);
            $females = array_fill(0, 16, null);

            foreach ($rows as $row) {
                $ageIndex = $this->ageGroupIndex($row[$ageColumn] ?? null);
                if ($ageIndex === null) {
                    continue;
                }
                $male = $this->numericValue($row[$maleColumn] ?? null);
                $female = $this->numericValue($row[$femaleColumn] ?? null);
                if ($male === null || $female === null || $male < 0 || $female < 0) {
                    continue;
                }
                $males[$ageIndex] = $male;
                $females[$ageIndex] = $female;
            }

            if (in_array(null, $males, true) || in_array(null, $females, true)) {
                continue;
            }

            return [
                'males' => array_map(fn ($v) => (int) round((float) $v), $males),
                'females' => array_map(fn ($v) => (int) round((float) $v), $females),
            ];
        }

        return null;
    }

    /**
     * Ambil pasangan header + baris data yang layak pakai dari satu PublicationTable.
     *
     * @return array{0: array<int, string>, 1: array<int, array<string, mixed>>}
     */
    protected function tableGrid(PublicationTable $table): array
    {
        $data = is_array($table->table_data) ? $table->table_data : [];
        $headers = array_values(array_filter((array) ($data['headers'] ?? []), 'is_scalar'));
        $rows = is_array($data['data'] ?? null) ? $data['data'] : [];
        $rows = array_values(array_filter($rows, 'is_array'));

        return [array_map('strval', $headers), $rows];
    }

    protected function detectColumnBy(array $headers, array $rows, callable $probe, int $minHits): ?string
    {
        foreach ($headers as $header) {
            if ($this->countMatching($headers, $rows, $header, $probe) >= $minHits) {
                return $header;
            }
        }

        return null;
    }

    protected function countMatching(array $headers, array $rows, string $header, callable $probe): int
    {
        $hits = 0;
        foreach ($rows as $row) {
            if ($probe($row[$header] ?? null)) {
                $hits++;
            }
        }

        return $hits;
    }

    protected function preferHeader(array $headers, string $pattern): ?string
    {
        foreach ($headers as $header) {
            if (preg_match($pattern, (string) $header)) {
                return $header;
            }
        }

        return null;
    }

    protected function allValuesAtMost(array $rows, string $header, float $limit): bool
    {
        $seen = 0;
        foreach ($rows as $row) {
            $value = $this->numericValue($row[$header] ?? null);
            if ($value === null) {
                continue;
            }
            $seen++;
            if ($value > $limit) {
                return false;
            }
        }

        return $seen >= 10;
    }

    /**
     * Indeks bulan 0-11 (Jan..Des) dari label sel; null bila bukan nama bulan.
     */
    protected function monthIndex(mixed $value): ?int
    {
        if (! is_string($value)) {
            return null;
        }

        $key = strtolower(trim(str_replace(['.', ','], '', $value)));

        $months = [
            'jan' => 0, 'januari' => 0,
            'feb' => 1, 'februari' => 1,
            'mar' => 2, 'maret' => 2,
            'apr' => 3, 'april' => 3,
            'mei' => 4, 'may' => 4,
            'jun' => 5, 'juni' => 5,
            'jul' => 6, 'juli' => 6,
            'agt' => 7, 'agustus' => 7, 'agu' => 7,
            'sep' => 8, 'september' => 8,
            'okt' => 9, 'oktober' => 9,
            'nov' => 10, 'november' => 10,
            'des' => 11, 'desember' => 11,
        ];

        return $months[$key] ?? null;
    }

    /**
     * Indeks kelompok umur 0-15 (0-4 sampai 75+) dari label sel; null bila bukan label umur.
     * Hanya label teks yang diterima sehingga angka biasa tidak dianggap kelompok umur.
     */
    protected function ageGroupIndex(mixed $value): ?int
    {
        if (! is_string($value)) {
            return null;
        }

        $key = strtolower(trim(str_replace(['.', ',', ' '], '', $value)));
        if ($key === '') {
            return null;
        }

        // Rentang "0-4", "5-9", ..., "70-74" (juga gaya "0 s/d 4" / "0sd4").
        if (preg_match('/^(\d{1,2})\D*?(\d{1,2})$/', $key, $pair)) {
            $start = (int) $pair[1];
            $end = (int) $pair[2];
            if ($start % 5 === 0 && $end === $start + 4 && $start <= 70) {
                return intdiv($start, 5);
            }
        }

        // "<5", "<5tahun"
        if (str_starts_with($key, '<') || str_starts_with($key, 'kurangdari')) {
            $first = preg_match('/(\d{1,2})/', $key, $m) ? (int) $m[1] : 99;

            return $first <= 5 ? 0 : null;
        }

        if (! preg_match('/(\d{1,2})/', $key, $m)) {
            return null;
        }

        $start = (int) $m[1];

        // "75+", "75tahunkeatas", "70 ke atas", ">=74"
        $upward = str_contains($key, 'keatas') || str_contains($key, 'diatas') || str_contains($key, 'atas')
            || str_ends_with($key, '+') || str_starts_with($key, '>');

        if ($upward && $start >= 70) {
            return 15;
        }

        return $start >= 75 ? 15 : null;
    }

    /**
     * Normalisasi sel angka (format Indonesia "1.234,5", Inggris "1,234.5", angka polos).
     */
    protected function numericValue(mixed $value): ?float
    {
        if (is_int($value) || is_float($value)) {
            return (float) $value;
        }

        if (! is_string($value)) {
            return null;
        }

        $raw = str_replace(["\u{00A0}", ' '], '', trim($value));
        if ($raw === '' || $raw === '-' || str_contains($raw, '%') || ! preg_match('/^-?\d/', $raw)) {
            return null;
        }

        if (preg_match('/^-?\d{1,3}(\.\d{3})+(,\d+)?$/', $raw)) {
            return (float) str_replace([',', '.'], ['', ''], $raw) + 0.0;
        }

        if (preg_match('/^-?\d{1,3}(,\d{3})+(\.\d+)?$/', $raw)) {
            return (float) str_replace(',', '', $raw);
        }

        if (preg_match('/^-?\d+,\d+$/', $raw)) {
            return (float) str_replace(',', '.', $raw);
        }

        if (preg_match('/^-?\d+(\.\d+)?$/', $raw)) {
            return (float) $raw;
        }

        return null;
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

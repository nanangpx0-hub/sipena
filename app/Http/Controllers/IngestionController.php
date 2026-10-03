<?php

namespace App\Http\Controllers;

use App\Models\Publication;
use App\Models\PublicationTable;
use App\Models\RawDataFile;
use App\Models\WorkflowLog;
use App\Services\AnomalyDetectionService;
use App\Services\FuzzyMatchService;
use App\Services\PythonWorkerService;
use App\Support\ActiveYear;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class IngestionController extends Controller
{
    protected PythonWorkerService $pythonService;

    protected FuzzyMatchService $fuzzyService;

    protected AnomalyDetectionService $anomalyService;

    public function __construct(
        PythonWorkerService $pythonService,
        FuzzyMatchService $fuzzyService,
        AnomalyDetectionService $anomalyService,
    ) {
        $this->pythonService = $pythonService;
        $this->fuzzyService = $fuzzyService;
        $this->anomalyService = $anomalyService;
    }

    /**
     * Tampilan daftar berkas mentah OPD dan formulir unggah
     */
    public function index(Request $request)
    {
        // Dropdown target publikasi hanya menampilkan tahun terbit aktif.
        $activeYear = ActiveYear::get();
        $publications = Publication::with('district')
            ->when($activeYear !== null, fn ($query) => $query->where('year', $activeYear))
            ->orderBy('title')->get();
        $rawFiles = RawDataFile::with(['publication', 'uploader'])->latest()->paginate(15);

        $commonOpds = [
            'Dinas Pendidikan',
            'Dinas Kesehatan',
            'Dinas Kependudukan dan Pencatatan Sipil',
            'Dinas Sosial',
            'Dinas Tenaga Kerja',
            'Dinas Tanaman Pangan, Hortikultura, dan Perkebunan',
            'Dinas Ketahanan Pangan dan Peternakan',
            'Dinas Perikanan',
            'Dinas Pariwisata dan Kebudayaan',
            'Dinas Koperasi dan Usaha Mikro',
            'Dinas Perindustrian dan Perdagangan',
            'Dinas Perhubungan',
            'Dinas Komunikasi dan Informatika',
            'Badan Pendapatan Daerah',
            'Badan Perencanaan Pembangunan Daerah',
            'Badan Penanggulangan Bencana Daerah',
            'Kantor Kementerian Agama Kabupaten Jember',
        ];

        // Distribusi berkas/tabel per bab publikasi (tahun aktif) — dihitung dari
        // PublicationTable agar grafik tidak menampilkan angka tebakan.
        $chapterLabels = [
            1 => 'Geografi & Iklim', 2 => 'Pemerintahan', 3 => 'Kependudukan & Ketenagakerjaan',
            4 => 'Sosial', 5 => 'Pertanian', 6 => 'Pariwisata', 7 => 'Perbankan',
            8 => 'Industri & Perdagangan', 9 => 'Transportasi & Komunikasi',
            10 => 'Keuangan & Harga', 11 => 'Pendapatan Regional', 12 => 'Perbandingan Regional',
            13 => 'Lain-lain',
        ];
        $tables = PublicationTable::query()
            ->when($activeYear !== null, fn ($query) => $query->whereHas('publication', fn ($q) => $q->where('year', $activeYear)))
            ->selectRaw('chapter_number, count(*) as total')
            ->groupBy('chapter_number')
            ->orderBy('chapter_number')
            ->pluck('total', 'chapter_number')
            ->toArray();
        $filesByChapter = collect($tables)
            ->map(fn ($count, $chapter) => ['chapter' => (int) $chapter, 'label' => 'Bab '.$chapter.' — '.($chapterLabels[(int) $chapter] ?? 'Lainnya'), 'count' => (int) $count])
            ->values()->all();

        // Distribusi berkas mentah per OPD. Berasal dari kolom opd_source_name
        // pada RawDataFile, sehingga grafik selalu mencerminkan berkas nyata.
        $filesByOpd = RawDataFile::query()
            ->when($activeYear !== null, fn ($query) => $query->whereHas('publication', fn ($q) => $q->where('year', $activeYear)))
            ->selectRaw('opd_source_name, count(*) as total')
            ->whereNotNull('opd_source_name')
            ->groupBy('opd_source_name')
            ->orderByDesc('total')
            ->limit(12)
            ->pluck('total', 'opd_source_name')
            ->map(fn ($count, $opd) => ['opd' => (string) $opd, 'count' => (int) $count])
            ->values()
            ->all();

        // Persentase penerimaan data dinas: OPD unik yang sudah mengirim berkas
        // dibanding daftar OPD potensial.
        $receivedOpdNames = RawDataFile::query()
            ->when($activeYear !== null, fn ($query) => $query->whereHas('publication', fn ($q) => $q->where('year', $activeYear)))
            ->distinct()->pluck('opd_source_name')->all();
        $receivedOpdCount = count($receivedOpdNames);
        $totalOpdCount = max(count($commonOpds), 1);
        $opdAcceptancePct = (int) round($receivedOpdCount * 100 / $totalOpdCount);
        $opdAcceptance = [
            'received' => $receivedOpdCount,
            'total' => count($commonOpds),
            'percent' => $opdAcceptancePct,
        ];

        // Petunjuk teknis struktur kolom per instansi dinas kunci.
        $opdGuides = [
            [
                'name' => 'Dinas Kependudukan dan Pencatatan Sipil',
                'short' => 'Dispendukcapil',
                'color' => 'sky',
                'chapter' => 'Bab 3 — Kependudukan & Ketenagakerjaan',
                'columns' => ['Kecamatan', 'Desa/Kelurahan', 'Laki-laki', 'Perempuan', 'Jumlah Penduduk', 'Kartu Keluarga'],
                'notes' => 'Nama desa wajib mengikuti kode wilayah BPS 3509; pisahkan gender per desa.',
            ],
            [
                'name' => 'Dinas Pendidikan',
                'short' => 'Dinas Pendidikan',
                'color' => 'rose',
                'chapter' => 'Bab 4 — Sosial',
                'columns' => ['Kecamatan', 'Desa', 'Jenjang', 'Nama Sekolah', 'Guru', 'Murid', 'Ruang Kelas'],
                'notes' => 'Gunakan mode AGGREGATE_SCHOOL agar agregasi sekolah per desa dihitung Python.',
            ],
            [
                'name' => 'Dinas Tanaman Pangan, Hortikultura, dan Perkebunan',
                'short' => 'Dinas Pertanian',
                'color' => 'emerald',
                'chapter' => 'Bab 5 — Pertanian',
                'columns' => ['Kecamatan', 'Komoditas', 'Luas Panen (ha)', 'Produksi (ton)', 'Produktivitas'],
                'notes' => 'Desimal wajib konsisten; engine menormalkan koma/titik secara otomatis.',
            ],
            [
                'name' => 'Dinas Sosial',
                'short' => 'Dinsos',
                'color' => 'amber',
                'chapter' => 'Bab 4 — Sosial',
                'columns' => ['Kecamatan', 'Desa', 'Jenis Bantuan', 'Penerima (jiwa)', 'Anggaran (Rp)'],
                'notes' => 'Hindari merge cells pada kolom Kecamatan; ulangi nama kecamatan tiap baris.',
            ],
        ];

        return view('ingestion.index', compact(
            'publications',
            'rawFiles',
            'activeYear',
            'commonOpds',
            'filesByChapter',
            'filesByOpd',
            'opdAcceptance',
            'opdGuides'
        ));
    }

    /**
     * Proses unggah berkas Excel OPD dengan versioning SHA-256 dan ekstraksi via Python
     */
    public function upload(Request $request)
    {
        $request->validate([
            'publication_id' => 'required|exists:publications,id',
            'opd_source_name' => 'required|string|max:255',
            'excel_file' => 'required|file|mimes:xlsx,xls,csv|max:20480',
            'chapter_number' => 'required|integer|min:1|max:13',
            'table_number' => 'nullable|string|max:50',
            'notes' => 'nullable|string|max:2000',
            'data_mode' => 'required|in:DIRECT,AGGREGATE_SCHOOL,SKD_VKD',
        ]);

        $file = $request->file('excel_file');
        $pub = Publication::findOrFail($request->publication_id);

        if ($pub->isLocked()) {
            return redirect()->back()->with('error', "Publikasi '{$pub->title}' berstatus {$pub->status} dan TERKUNCI. Unggahan data baru ditolak oleh sistem.");
        }

        $originalFilename = $file->getClientOriginalName();
        $sha256 = hash_file('sha256', $file->getRealPath());

        // Hitung versi berkas sebelumnya untuk OPD yang sama
        $prevVersion = RawDataFile::where('publication_id', $pub->id)
            ->where('opd_source_name', $request->opd_source_name)
            ->max('version_number') ?? 0;
        $versionNumber = $prevVersion + 1;

        // Path penyimpanan terstruktur: storage/app/private -> akses via storage_path('app/private/...')
        // SELF-HEAL FIX: versi lama memakai storage_path("raw_excel/...") (= storage/raw_excel)
        // sedangkan $savedPath memakai prefix "storage/..." yang di-resolve Python via base_path(),
        // sehingga file tidak ditemukan. Sekarang konsisten memakai storage/app/private.
        $safeAgency = Str::slug($request->opd_source_name);
        $timestamp = now()->format('Ymd_His');
        $safeFilename = "{$timestamp}_v{$versionNumber}_".Str::slug(pathinfo($originalFilename, PATHINFO_FILENAME)).'.'.$file->getClientOriginalExtension();
        $relativeDir = "raw_excel/{$pub->year}/{$safeAgency}";

        $fullDirPath = storage_path('app'.DIRECTORY_SEPARATOR.'private'.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $relativeDir));
        if (! is_dir($fullDirPath)) {
            mkdir($fullDirPath, 0755, true);
        }

        $file->move($fullDirPath, $safeFilename);
        // Path absolut dipakai Python; kolom storage_path menyimpan path relatif utk audit.
        $absoluteSavedPath = $fullDirPath.DIRECTORY_SEPARATOR.$safeFilename;
        $savedPath = "storage/app/private/{$relativeDir}/{$safeFilename}";

        $userId = Auth::id();

        // Simpan metadata berkas mentah (immutable raw file record)
        $rawFileRecord = RawDataFile::create([
            'publication_id' => $pub->id,
            'opd_source_name' => $request->opd_source_name,
            'original_filename' => $originalFilename,
            'storage_path' => $savedPath,
            'file_hash_sha256' => $sha256,
            'version_number' => $versionNumber,
            'uploaded_by' => $userId,
            'notes' => $request->notes,
            'status' => 'INGESTED',
        ]);

        // Ekstraksi data via Python Worker Engine
        // SELF-HEAL FIX: Python script menerima ABSOLUTE path Windows; path relatif
        // "storage/..." sebelumnya di-resolve via base_path() menjadi lokasi yang salah.
        $cleanedData = [];
        try {
            // Resolve ke path absolut yang benar sesuai lokasi penyimpanan aktual.
            $pythonInputPath = $absoluteSavedPath;
            if (! file_exists($pythonInputPath)) {
                $candidates = [
                    base_path(str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $savedPath)),
                    storage_path(str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $relativeDir).DIRECTORY_SEPARATOR.$safeFilename),
                ];
                foreach ($candidates as $cand) {
                    if (file_exists($cand)) {
                        $pythonInputPath = $cand;
                        break;
                    }
                }
            }
            if ($request->data_mode === 'AGGREGATE_SCHOOL') {
                $cleanedData = $this->pythonService->aggregateSchools($pythonInputPath, $pub->type === 'KDA' ? 'desa' : 'kecamatan');
            } elseif ($request->data_mode === 'SKD_VKD') {
                $outputSvgAbs = base_path('storage'.DIRECTORY_SEPARATOR.'custom_assets'.DIRECTORY_SEPARATOR."skd_cartesian_{$pub->id}.svg");
                $cleanedData = $this->pythonService->runSkdEngine($pythonInputPath, $outputSvgAbs);
            } else {
                $cleanedData = $this->pythonService->cleanExcel($pythonInputPath);
            }

            // Guard integritas: worker Python wajib melaporkan sukses.
            // Berkas mentah tetap tersimpan (audit trail SHA-256), tetapi tidak ada
            // tabel yang disimpan & status tidak berubah bila ekstraksi gagal.
            $workerStatus = $cleanedData['status'] ?? 'error';
            if ($workerStatus !== 'success') {
                $rawFileRecord->update([
                    'status' => 'FAILED',
                    'notes' => trim(($request->notes ? $request->notes.' | ' : '').'Ekstraksi gagal: '.($cleanedData['message'] ?? 'output worker tidak valid')),
                ]);
                WorkflowLog::create([
                    'publication_id' => $pub->id,
                    'from_status' => $pub->status,
                    'to_status' => $pub->status,
                    'user_id' => $userId,
                    'remarks' => "Ekstraksi Python GAGAL untuk {$originalFilename}: ".($cleanedData['message'] ?? 'output tidak valid'),
                ]);

                return redirect()->back()->with('error', "Berkas '{$originalFilename}' tersimpan (v{$versionNumber}) tetapi ekstraksi Python GAGAL: ".($cleanedData['message'] ?? 'output worker tidak valid').' Perbaiki berkas lalu unggah ulang.');
            }

            // Simpan ke PublicationTable
            $tableNum = $request->table_number ?: "{$request->chapter_number}.1";

            // Deteksi anomali: lonjakan/penurunan >= 50% atau nilai negatif
            // dibandingkan tabel & bab yang sama pada tahun sebelumnya (t-1).
            $cleanedData['warnings'] = $this->anomalyService->detect(
                $cleanedData,
                $this->previousYearTable($pub, (int) $request->chapter_number, $tableNum)?->table_data,
            );

            // Fuzzy matching nama desa hasil ekstraksi terhadap master desa
            // kecamatan terkait (khusus ingesti KDA) untuk koreksi baku.
            $villageSuggestions = $this->villageSuggestions($pub, $cleanedData);
            if ($villageSuggestions === []) {
                unset($cleanedData['village_suggestions']);
            } else {
                $cleanedData['village_suggestions'] = $villageSuggestions;
            }

            PublicationTable::updateOrCreate(
                [
                    'publication_id' => $pub->id,
                    'chapter_number' => $request->chapter_number,
                    'table_number' => $tableNum,
                ],
                [
                    'title_id' => "Data {$request->opd_source_name} - Bab {$request->chapter_number}",
                    'title_en' => "Data of {$request->opd_source_name} - Chapter {$request->chapter_number}",
                    'source_agency' => $request->opd_source_name,
                    'table_data' => $cleanedData,
                    'is_verified' => false,
                ]
            );

            // Update status publikasi menjadi DATA_INGESTED (via state machine)
            if ($pub->canTransitionTo('DATA_INGESTED')) {
                $pub->transitionTo(
                    'DATA_INGESTED',
                    $userId,
                    "Unggah berkas OPD {$request->opd_source_name} berhasil diekstraksi oleh Python Worker."
                );
            }

            return redirect()->back()->with('success', "Berkas '{$originalFilename}' berhasil diunggah (v{$versionNumber}) dan data berhasil diekstraksi ke database!");
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Unggah berhasil namun ekstraksi Python mengalami kendala: '.$e->getMessage());
        }
    }

    /**
     * Tabel & bab yang sama milik publikasi tahun sebelumnya (t-1) sebagai pembanding.
     */
    protected function previousYearTable(Publication $pub, int $chapterNumber, string $tableNumber): ?PublicationTable
    {
        $previousPublication = Publication::query()
            ->where('type', $pub->type)
            ->where('year', $pub->year - 1)
            ->when(
                $pub->district_id === null,
                fn ($query) => $query->whereNull('district_id'),
                fn ($query) => $query->where('district_id', $pub->district_id),
            )
            ->first();

        if (! $previousPublication) {
            return null;
        }

        return PublicationTable::query()
            ->where('publication_id', $previousPublication->id)
            ->where('chapter_number', $chapterNumber)
            ->where('table_number', $tableNumber)
            ->first();
    }

    /**
     * Sugesti koreksi baku nama desa (fuzzy matching) untuk ingesti KDA.
     *
     * @return array<int, array<string, mixed>>
     */
    protected function villageSuggestions(Publication $pub, array $cleanedData): array
    {
        if ($pub->type !== 'KDA' || $pub->district_id === null) {
            return [];
        }

        $rows = is_array($cleanedData['data'] ?? null) ? $cleanedData['data'] : [];
        if ($rows === []) {
            return [];
        }

        $headers = array_values(array_filter((array) ($cleanedData['headers'] ?? []), 'is_scalar'));
        if ($headers === []) {
            $union = [];
            foreach ($rows as $row) {
                foreach (array_keys($row) as $key) {
                    $union[(string) $key] = true;
                }
            }
            $headers = array_keys($union);
        }

        $column = $this->fuzzyService->villageColumn(array_map('strval', $headers));
        if ($column === null) {
            return [];
        }

        $suggestions = [];
        $seen = [];

        foreach ($rows as $index => $row) {
            $value = $row[$column] ?? null;
            if (! is_string($value) || trim($value) === '') {
                continue;
            }

            $suggestion = $this->fuzzyService->suggestVillage($value, (int) $pub->district_id);
            if ($suggestion === null) {
                continue;
            }

            $dedupeKey = strtolower($suggestion['raw_name']).'|'.$suggestion['official_name'];
            if (isset($seen[$dedupeKey])) {
                continue;
            }
            $seen[$dedupeKey] = true;

            $suggestion['row'] = $index + 1;
            $suggestion['column'] = $column;
            $suggestions[] = $suggestion;

            if (count($suggestions) >= 25) {
                break;
            }
        }

        return $suggestions;
    }

    /**
     * Unduh template berkas Excel (.xlsx) resmi untuk Ingesti OPD
     */
    public function downloadTemplate(Request $request, string $type)
    {
        $validTypes = ['standard', 'schools', 'skd', 'direct', 'dapodik', 'vkd'];
        if (! in_array(strtolower($type), $validTypes, true)) {
            abort(404, 'Tipe template tidak valid');
        }

        // Normalize alias
        $normType = match (strtolower($type)) {
            'direct' => 'standard',
            'dapodik' => 'schools',
            'vkd' => 'skd',
            default => strtolower($type),
        };

        $publicationId = $request->query('publication_id');
        $pub = $publicationId ? Publication::with(['district.villages'])->find($publicationId) : null;

        $options = [];
        $filename = "Template_SI-PENA_{$normType}.xlsx";

        if ($normType === 'standard') {
            if ($pub && $pub->type === 'KDA' && $pub->district) {
                $options['scope'] = 'kda';
                $options['district'] = $pub->district->name;
                $villages = $pub->district->villages()->pluck('name')->toArray();
                if (! empty($villages)) {
                    $options['villages'] = $villages;
                }
                $safeDist = Str::slug($pub->district->name);
                $filename = "Template_Tabel_KDA_{$safeDist}.xlsx";
            } else {
                $options['scope'] = 'dda';
                $filename = 'Template_Tabel_Standar_DDA_Jember.xlsx';
            }
        } elseif ($normType === 'schools') {
            if ($pub && $pub->district) {
                $options['district'] = $pub->district->name;
                $safeDist = Str::slug($pub->district->name);
                $filename = "Template_Dapodik_EMIS_{$safeDist}.xlsx";
            } else {
                $filename = 'Template_Dapodik_EMIS_Jember.xlsx';
            }
        } elseif ($normType === 'skd') {
            $filename = 'Template_Kuesioner_VKD_SKD.xlsx';
        }

        $tempDir = storage_path('temp'.DIRECTORY_SEPARATOR.'templates');
        if (! is_dir($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        $outputPath = $tempDir.DIRECTORY_SEPARATOR.uniqid('tmpl_', true).'_'.$filename;

        try {
            $this->pythonService->generateExcelTemplate($normType, $outputPath, $options);

            if (! file_exists($outputPath)) {
                return redirect()->back()->with('error', 'Gagal membuat template Excel.');
            }

            return response()->download($outputPath, $filename, [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ])->deleteFileAfterSend(true);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kendala saat menghasilkan template: '.$e->getMessage());
        }
    }

    /**
     * Unduh berkas Excel mentah asli dari arsip audit
     */
    public function downloadRawFile(RawDataFile $rawFile)
    {
        $candidatePaths = [
            base_path(str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $rawFile->storage_path)),
            storage_path('app'.DIRECTORY_SEPARATOR.'private'.DIRECTORY_SEPARATOR.str_replace(['storage/app/private/', 'storage/app/private\\'], '', $rawFile->storage_path)),
            storage_path(str_replace(['storage/', 'storage\\'], '', $rawFile->storage_path)),
        ];

        $filePath = null;
        foreach ($candidatePaths as $cand) {
            if (is_file($cand)) {
                $filePath = $cand;
                break;
            }
        }

        if (! $filePath) {
            return redirect()->back()->with('error', "Berkas '{$rawFile->original_filename}' tidak ditemukan di penyimpanan server.");
        }

        return response()->download($filePath, $rawFile->original_filename);
    }

    /**
     * Prapinjau data tabel yang berhasil diekstrak oleh worker Python
     */
    public function previewExtractedData(RawDataFile $rawFile)
    {
        $tables = PublicationTable::where('publication_id', $rawFile->publication_id)
            ->where('source_agency', $rawFile->opd_source_name)
            ->get();

        if ($tables->isEmpty()) {
            $tables = PublicationTable::where('publication_id', $rawFile->publication_id)->latest()->take(3)->get();
        }

        return response()->json([
            'raw_file' => [
                'id' => $rawFile->id,
                'filename' => $rawFile->original_filename,
                'opd_source_name' => $rawFile->opd_source_name,
                'version' => $rawFile->version_number,
                'sha256' => $rawFile->file_hash_sha256,
                'status' => $rawFile->status,
                'notes' => $rawFile->notes,
                'created_at' => $rawFile->created_at->format('d/m/Y H:i:s'),
            ],
            'tables' => $tables->map(fn ($t) => [
                'id' => $t->id,
                'table_number' => $t->table_number,
                'chapter_number' => $t->chapter_number,
                'title_id' => $t->title_id,
                'source_agency' => $t->source_agency,
                'data' => $t->table_data,
            ]),
        ]);
    }
}

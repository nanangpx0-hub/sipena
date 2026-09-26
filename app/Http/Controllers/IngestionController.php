<?php

namespace App\Http\Controllers;

use App\Models\Publication;
use App\Models\PublicationTable;
use App\Models\RawDataFile;
use App\Models\WorkflowLog;
use App\Services\FuzzyMatchService;
use App\Services\PythonWorkerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class IngestionController extends Controller
{
    protected PythonWorkerService $pythonService;

    protected FuzzyMatchService $fuzzyService;

    public function __construct(PythonWorkerService $pythonService, FuzzyMatchService $fuzzyService)
    {
        $this->pythonService = $pythonService;
        $this->fuzzyService = $fuzzyService;
    }

    /**
     * Tampilan daftar berkas mentah OPD dan formulir unggah
     */
    public function index(Request $request)
    {
        $publications = Publication::with('district')->orderBy('title')->get();
        $rawFiles = RawDataFile::with(['publication', 'uploader'])->latest()->paginate(15);

        return view('ingestion.index', compact('publications', 'rawFiles'));
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
}

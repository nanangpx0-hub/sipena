<?php

namespace App\Http\Controllers;

use App\Jobs\CompilePublicationJob;
use App\Models\Publication;
use App\Models\WorkflowLog;
use App\Services\TypstCompilerService;
use App\Support\ActiveYear;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class CompilationController extends Controller
{
    protected TypstCompilerService $typstService;

    public function __construct(TypstCompilerService $typstService)
    {
        $this->typstService = $typstService;
    }

    /**
     * Dashboard pemantauan kompilasi publikasi dan antrean masal
     */
    public function index()
    {
        $activeYear = ActiveYear::get();

        $publications = Publication::with('district')
            ->when($activeYear !== null, fn ($query) => $query->where('year', $activeYear))
            ->orderBy('type')->orderBy('title')->get();

        $totalKda = Publication::where('type', 'KDA')
            ->when($activeYear !== null, fn ($query) => $query->where('year', $activeYear))
            ->count();
        $releasedKda = Publication::where('type', 'KDA')->where('status', 'FINAL_RELEASED')
            ->when($activeYear !== null, fn ($query) => $query->where('year', $activeYear))
            ->count();
        $approvedKda = Publication::where('type', 'KDA')->where('status', 'APPROVED_LOCKED')
            ->when($activeYear !== null, fn ($query) => $query->where('year', $activeYear))
            ->count();
        $pendingJobs = DB::table('jobs')->count();
        $failedJobs = Schema::hasTable('failed_jobs') ? DB::table('failed_jobs')->count() : 0;

        // Distribusi status 31 KDA tahun aktif untuk bar multi-status & pie chart.
        $kdaStatusCounts = Publication::where('type', 'KDA')
            ->when($activeYear !== null, fn ($query) => $query->where('year', $activeYear))
            ->selectRaw('status, count(*) as count')
            ->groupBy('status')->pluck('count', 'status')->toArray();

        // Performa render Typst terakhir (durasi terukur, bukan tebakan).
        $typstPerf = $this->lastCompilePerf();
        $workerLogTail = $this->workerLogTail(30);
        // Kartu job antrean real-time (dibaca dari tabel jobs Laravel).
        $queueSnapshot = $this->queueSnapshot();

        return view('compilation.index', compact(
            'publications',
            'totalKda',
            'releasedKda',
            'approvedKda',
            'pendingJobs',
            'failedJobs',
            'activeYear',
            'kdaStatusCounts',
            'typstPerf',
            'workerLogTail',
            'queueSnapshot'
        ));
    }

    /**
     * Status antrean kompilasi dalam format JSON untuk polling Alpine.js
     * (dipanggil tiap 4 detik selama masih ada job pada antrean).
     */
    public function queueStatus(): JsonResponse
    {
        $activeYear = ActiveYear::get();
        $scope = fn ($query) => $activeYear !== null ? $query->where('year', $activeYear) : $query;

        return response()->json([
            'pendingJobs' => DB::table('jobs')->count(),
            'failedJobs' => Schema::hasTable('failed_jobs') ? DB::table('failed_jobs')->count() : 0,
            'totalKda' => $scope(Publication::where('type', 'KDA'))->count(),
            'approvedKda' => $scope(Publication::where('type', 'KDA')->where('status', 'APPROVED_LOCKED'))->count(),
            'releasedKda' => $scope(Publication::where('type', 'KDA')->where('status', 'FINAL_RELEASED'))->count(),
            'activeYear' => $activeYear,
            'queue' => $this->queueSnapshot(),
        ]);
    }

    /**
     * Daftar antrean kompilasi yang sedang berjalan untuk kartu job real-time.
     *
     * Membaca tabel `jobs` dan `job_batches` milik Laravel. Job yang sedang
     * diproses ditandai `reserved_at`, job yang belum tersentuh `created_at`.
     * Tidak ada angka tebakan: setiap kartu berasal dari baris tabel nyata.
     *
     * @return array<int, array<string, mixed>>
     */
    protected function queueSnapshot(): array
    {
        if (! Schema::hasTable('jobs')) {
            return [];
        }

        $snapshot = [];

        // Job aktif: sudah di-reserve worker namun belum selesai diproses.
        $reserved = DB::table('jobs')
            ->whereNotNull('reserved_at')
            ->orderByDesc('reserved_at')
            ->limit(6)
            ->get(['id', 'payload', 'queue', 'attempts', 'reserved_at', 'created_at']);

        foreach ($reserved as $job) {
            $snapshot[] = [
                'id' => (int) $job->id,
                'label' => $this->jobLabel($job->payload),
                'queue' => $job->queue ?: 'default',
                'attempts' => (int) $job->attempts,
                'state' => 'processing',
                'since' => $job->reserved_at,
            ];
        }

        // Antrean menunggu: belum di-reserve, ditampilkan setelah job aktif.
        $waiting = DB::table('jobs')
            ->whereNull('reserved_at')
            ->orderBy('created_at')
            ->limit(max(0, 6 - count($snapshot)))
            ->get(['id', 'payload', 'queue', 'attempts', 'created_at']);

        foreach ($waiting as $job) {
            $snapshot[] = [
                'id' => (int) $job->id,
                'label' => $this->jobLabel($job->payload),
                'queue' => $job->queue ?: 'default',
                'attempts' => (int) $job->attempts,
                'state' => 'waiting',
                'since' => $job->created_at,
            ];
        }

        return $snapshot;
    }

    /**
     * Nama job yang dapat dibaca manusia, diambil dari `displayName` pada
     * payload queue Laravel (nama kelas job) tanpa kolom tambahan apa pun.
     */
    protected function jobLabel(?string $payload): string
    {
        $displayName = json_decode((string) $payload, true)['displayName'] ?? null;

        return is_string($displayName) && $displayName !== ''
            ? class_basename($displayName)
            : 'Kompilasi Publikasi';
    }

    /**
     * Performa kompilasi Typst terakhir dari catatan transisi (durasi nyata
     * yang ditulis oleh job ke WorkflowLog). Tanpa catatan maka "belum ada data".
     */
    protected function lastCompilePerf(): ?array
    {
        $log = WorkflowLog::query()
            ->where('remarks', 'like', '%DURASI_MS=%')
            ->latest()->first();

        if (! $log || ! preg_match('/DURASI_MS=(\d+)\s+HALAMAN=(\d+)?/', (string) $log->remarks, $m)) {
            return null;
        }

        $durationMs = (int) $m[1];
        $pages = isset($m[2]) && (int) $m[2] > 0 ? (int) $m[2] : null;

        return [
            'duration_ms' => $durationMs,
            'pages' => $pages,
            'ms_per_page' => $pages !== null ? round($durationMs / $pages, 1) : null,
            'title' => $log->publication?->title ?? 'Publikasi',
            'when' => $log->created_at?->format('d/m/Y H:i'),
        ];
    }

    /**
     * Ekor log worker dari berkas log Laravel (terpotong aman di akhir berkas).
     */
    protected function workerLogTail(int $lines = 30): array
    {
        try {
            $path = storage_path('logs'.DIRECTORY_SEPARATOR.'laravel.log');
            if (! is_file($path)) {
                return [];
            }

            $handle = fopen($path, 'rb');
            if ($handle === false) {
                return [];
            }

            $buffer = '';
            fseek($handle, 0, SEEK_END);
            $pos = ftell($handle);
            $chunk = 4096;
            while ($pos > 0 && substr_count($buffer, "\n") < $lines) {
                $read = min($chunk, $pos);
                $pos -= $read;
                fseek($handle, $pos);
                $buffer = fread($handle, $read).$buffer;
            }
            fclose($handle);

            $all = array_values(array_filter(array_map('trim', explode("\n", $buffer))));

            return array_slice($all, -$lines);
        } catch (\Throwable $e) {
            return [];
        }
    }

    /**
     * Kompilasi instan satu publikasi
     */
    public function compileSingle(Request $request, int $id)
    {
        $pub = Publication::findOrFail($id);

        // Dispatch job or run synchronously if requested
        if ($request->boolean('sync', false)) {
            dispatch_sync(new CompilePublicationJob($pub->id, Auth::id()));

            return redirect()->back()->with('success', "Kompilasi PDF untuk '{$pub->title}' selesai!");
        }

        dispatch(new CompilePublicationJob($pub->id, Auth::id()));

        return redirect()->back()->with('success', "Job kompilasi untuk '{$pub->title}' telah dimasukkan ke dalam antrean.");
    }

    /**
     * Kompilasi Masal Seluruh 31 Kecamatan (KDA) pada TAHUN AKTIF saja.
     */
    public function batchCompileAllKDA()
    {
        $activeYear = ActiveYear::get();

        $kdaList = Publication::where('type', 'KDA')
            ->when($activeYear !== null, fn ($query) => $query->where('year', $activeYear))
            ->get();
        $count = 0;

        foreach ($kdaList as $pub) {
            dispatch(new CompilePublicationJob($pub->id, Auth::id()));
            $count++;
        }

        return redirect()->route('compilation.index')
            ->with('success', "Berhasil mendispatch {$count} antrean kompilasi KDA tahun ".($activeYear ?? '-').' ke dalam antrean server!');
    }

    /**
     * Unduh file PDF hasil kompilasi
     */
    public function downloadPdf(int $id)
    {
        $pub = Publication::findOrFail($id);
        $safeTitle = Str::slug($pub->title);
        // FIX: job menulis ke storage/output_pdf/{year}/ — selaraskan pembaca (blade memakai path yang sama).
        $filePath = storage_path('output_pdf'.DIRECTORY_SEPARATOR.$pub->year.DIRECTORY_SEPARATOR."{$safeTitle}.pdf");

        if (! file_exists($filePath)) {
            return redirect()->back()->with('error', 'Berkas PDF belum tersedia atau belum selesai dikompilasi.');
        }

        return response()->download($filePath, "{$safeTitle}.pdf");
    }
}

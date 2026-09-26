<?php

namespace App\Http\Controllers;

use App\Models\Publication;
use App\Services\PythonWorkerService;
use App\Support\ActiveYear;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SkdController extends Controller
{
    protected PythonWorkerService $pythonService;

    public function __construct(PythonWorkerService $pythonService)
    {
        $this->pythonService = $pythonService;
    }

    /**
     * Dashboard interaktif analisis SKD BPS Jember.
     *
     * ATURAN INTEGRITAS: halaman ini TIDAK PERNAH menampilkan angka hasil
     * tebakan/sistem. Bila mesin SKD belum memiliki berkas VKD sah, tampil
     * kondisi "belum ada data" beserta alasannya.
     *
     * ATURAN MULTI-TAHUN: SVG dan cache metrik diisolasi per publikasi
     * (skd_cartesian_{id}.svg / skd_metrics_{id}.json) sehingga tahun 2026 dan
     * 2027 tidak saling menimpa.
     */
    public function index(Request $request)
    {
        $skdYears = Publication::where('type', 'SKD')
            ->select('year')->distinct()->orderByDesc('year')
            ->pluck('year')->map(fn ($year) => (int) $year)->values()->all();

        $requestedYear = $request->filled('year') ? (int) $request->integer('year') : null;
        $year = $requestedYear ?? ActiveYear::get();

        $publication = Publication::where('type', 'SKD')
            ->when($year !== null, fn ($query) => $query->where('year', $year))
            ->latest()->first()
            ?? Publication::where('type', 'SKD')->latest()->first();

        $svgPath = $this->svgRelPath($publication?->id);
        $metricsPath = $this->metricsCachePath($publication?->id);
        // Kompatibilitas: berkas lama (sebelum pemisahan per publikasi) tetap
        // dibaca bila berkas per publikasi belum pernah dibuat.
        $legacyMetricsPath = storage_path('temp'.DIRECTORY_SEPARATOR.'skd_metrics_cache.json');
        if (! file_exists($metricsPath) && file_exists($legacyMetricsPath)) {
            $metricsPath = $legacyMetricsPath;
        }

        $metrics = null;
        $errorMessage = null;

        try {
            $svgFull = base_path(str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $svgPath));
            $cacheFresh = file_exists($metricsPath)
                && (time() - filemtime($metricsPath) < 86400)
                && (! file_exists($svgFull) || filemtime($metricsPath) >= filemtime($svgFull) - 5);

            if ($cacheFresh) {
                $cached = json_decode((string) file_get_contents($metricsPath), true);
                if (is_array($cached) && ($cached['status'] ?? '') === 'success') {
                    $metrics = $cached;
                }
            }

            if ($metrics === null) {
                // Tanpa berkas, engine SKD akan melaporkan error (tanpa angka tebakan).
                $result = $this->pythonService->runSkdEngine(null, $svgFull);
                if (($result['status'] ?? '') === 'success') {
                    $metrics = $result;
                    @file_put_contents($this->metricsCachePath($publication?->id), json_encode($result, JSON_UNESCAPED_UNICODE));
                } else {
                    $errorMessage = $result['message'] ?? 'Mesin SKD belum menerima berkas kuesioner VKD yang sah.';
                }
            }
        } catch (\Throwable $e) {
            $errorMessage = 'Mesin SKD tidak dapat dijalankan: '.$e->getMessage();
            Log::warning('Mesin SKD tidak dapat dijalankan: '.$e->getMessage());
        }

        return view('skd.index', [
            'publication' => $publication,
            'metrics' => $metrics ?: [],
            'svgPath' => $svgPath,
            'skdYears' => $skdYears,
            'selectedYear' => $publication?->year,
            'errorMessage' => $errorMessage,
        ]);
    }

    /**
     * Unggah berkas kuesioner mentah VKD baru (untuk tahun terbit aktif/terpilih).
     */
    public function upload(Request $request)
    {
        $request->validate([
            'vkd_file' => 'required|file|mimes:xlsx,xls,csv|max:20480',
        ]);

        $publication = $this->targetPublication($request);

        $file = $request->file('vkd_file');
        $tempDir = storage_path('temp');
        if (! is_dir($tempDir)) {
            mkdir($tempDir, 0755, true);
        }
        $file->move($tempDir, 'uploaded_vkd.'.$file->getClientOriginalExtension());
        $tempPath = $tempDir.DIRECTORY_SEPARATOR.'uploaded_vkd.'.$file->getClientOriginalExtension();

        $svgPath = $this->svgRelPath($publication?->id);
        $svgAbs = base_path(str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $svgPath));

        try {
            $metrics = $this->pythonService->runSkdEngine($tempPath, $svgAbs);
        } catch (\Throwable $e) {
            Log::warning('Unggahan SKD gagal: '.$e->getMessage());

            return redirect()->route('skd.index', $publication ? ['year' => $publication->year] : [])
                ->with('error', 'Pemrosesan berkas VKD gagal: '.$e->getMessage());
        }

        if (($metrics['status'] ?? '') !== 'success') {
            return redirect()->route('skd.index', $publication ? ['year' => $publication->year] : [])
                ->with(
                    'error',
                    'Berkas VKD ditolak: '.($metrics['message'] ?? 'format kolom tidak dikenali.')
                );
        }

        @file_put_contents($this->metricsCachePath($publication?->id), json_encode($metrics, JSON_UNESCAPED_UNICODE));

        return redirect()->route('skd.index', $publication ? ['year' => $publication->year] : [])
            ->with(
                'success',
                "Berkas VKD berhasil diproses! Nilai IKK: {$metrics['ikk_score']} ({$metrics['mutu_pelayanan']})."
            );
    }

    /**
     * Publikasi SKD target unggahan: tahun pada request, lalu tahun aktif,
     * lalu publikasi SKD terbaru.
     */
    protected function targetPublication(Request $request): ?Publication
    {
        $requestedYear = $request->filled('year') ? (int) $request->integer('year') : null;
        $year = $requestedYear ?? ActiveYear::get();

        return Publication::where('type', 'SKD')
            ->when($year !== null, fn ($query) => $query->where('year', $year))
            ->latest()->first()
            ?? Publication::where('type', 'SKD')->latest()->first();
    }

    /**
     * Path SVG relatif untuk publikasi tertentu; tanpa publikasi memakai
     * nama generik lama agar halaman tetap dapat dirender.
     */
    protected function svgRelPath(?int $publicationId): string
    {
        if ($publicationId === null) {
            return 'storage/custom_assets/skd_cartesian.svg';
        }

        $perPub = 'storage/custom_assets/skd_cartesian_'.$publicationId.'.svg';

        if (! file_exists(base_path(str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $perPub)))
            && file_exists(base_path('storage'.DIRECTORY_SEPARATOR.'custom_assets'.DIRECTORY_SEPARATOR.'skd_cartesian.svg'))) {
            return 'storage/custom_assets/skd_cartesian.svg';
        }

        return $perPub;
    }

    protected function metricsCachePath(?int $publicationId): string
    {
        if ($publicationId === null) {
            return storage_path('temp'.DIRECTORY_SEPARATOR.'skd_metrics_cache.json');
        }

        return storage_path('temp'.DIRECTORY_SEPARATOR.'skd_metrics_'.$publicationId.'.json');
    }
}

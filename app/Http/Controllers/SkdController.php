<?php

namespace App\Http\Controllers;

use App\Models\Publication;
use App\Services\PythonWorkerService;
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
     */
    public function index()
    {
        $publication = Publication::where('type', 'SKD')->latest()->first();

        $svgPath = 'storage/custom_assets/skd_cartesian.svg';
        $metricsPath = storage_path('temp/skd_metrics_cache.json');
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
                $result = $this->pythonService->runSkdEngine(null, $svgPath);
                if (($result['status'] ?? '') === 'success') {
                    $metrics = $result;
                    @file_put_contents($metricsPath, json_encode($result, JSON_UNESCAPED_UNICODE));
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
            'errorMessage' => $errorMessage,
        ]);
    }

    /**
     * Unggah berkas kuesioner mentah VKD baru.
     */
    public function upload(Request $request)
    {
        $request->validate([
            'vkd_file' => 'required|file|mimes:xlsx,xls,csv|max:20480',
        ]);

        $file = $request->file('vkd_file');
        $tempDir = storage_path('temp');
        if (! is_dir($tempDir)) {
            mkdir($tempDir, 0755, true);
        }
        $file->move($tempDir, 'uploaded_vkd.'.$file->getClientOriginalExtension());
        $tempPath = $tempDir.DIRECTORY_SEPARATOR.'uploaded_vkd.'.$file->getClientOriginalExtension();

        $svgPath = 'storage/custom_assets/skd_cartesian.svg';
        $svgAbs = base_path('storage'.DIRECTORY_SEPARATOR.'custom_assets'.DIRECTORY_SEPARATOR.'skd_cartesian.svg');

        try {
            $metrics = $this->pythonService->runSkdEngine($tempPath, $svgAbs);
        } catch (\Throwable $e) {
            Log::warning('Unggahan SKD gagal: '.$e->getMessage());

            return redirect()->route('skd.index')->with('error', 'Pemrosesan berkas VKD gagal: '.$e->getMessage());
        }

        if (($metrics['status'] ?? '') !== 'success') {
            return redirect()->route('skd.index')->with(
                'error',
                'Berkas VKD ditolak: '.($metrics['message'] ?? 'format kolom tidak dikenali.')
            );
        }

        @file_put_contents(storage_path('temp/skd_metrics_cache.json'), json_encode($metrics, JSON_UNESCAPED_UNICODE));

        return redirect()->route('skd.index')->with(
            'success',
            "Berkas VKD berhasil diproses! Nilai IKK: {$metrics['ikk_score']} ({$metrics['mutu_pelayanan']})."
        );
    }
}

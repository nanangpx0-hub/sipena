<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Symfony\Component\Process\Process;

/**
 * Pelapor status nyata mesin tri-bahasa SI-PENA.
 *
 * AGENTS.md §1.2 mensyaratkan pemisahan tegas antara Laravel (backend),
 * Python (worker analitis), dan Typst (typesetting). Footer serta kartu login
 * menampilkan indikator versi engine; indikator tersebut HARUS berasal dari
 * probe berkas biner sungguhan, bukan versi yang diketik manual di template,
 * supaya tidak pernah berbeda dengan kondisi server sebenarnya.
 *
 * Hasil probe di-cache agar halaman dashboard tidak memanggil proses eksternal
 * pada setiap render.
 */
class EngineStatusService
{
    /** Versi minimum Python sesuai AGENTS.md §1.2. */
    public const PYTHON_MINIMUM = '3.11';

    private const CACHE_KEY = 'sipena.engine_status.v1';

    private const CACHE_TTL_SECONDS = 3600;

    /**
     * @return array{
     *     laravel: array{label: string, ok: bool},
     *     python: array{label: string, ok: bool, detail: string, minimum: bool},
     *     typst: array{label: string, ok: bool},
     *     offline_ready: bool
     * }
     */
    public function status(): array
    {
        return Cache::remember(self::CACHE_KEY, self::CACHE_TTL_SECONDS, fn () => $this->probe());
    }

    /**
     * @return array<string, array<string, bool|string>>
     */
    private function probe(): array
    {
        $python = $this->probePython();

        return [
            'laravel' => [
                'label' => 'Laravel '.substr((string) app()->version(), 0, 3),
                'ok' => true,
            ],
            'python' => $python,
            'typst' => $this->probeTypst(),
            'offline_ready' => $this->hasExternalAssetReference(),
        ];
    }

    /**
     * @return array{label: string, ok: bool, detail: string, minimum: bool}
     */
    private function probePython(): array
    {
        $candidates = [
            base_path('python_engine'.DIRECTORY_SEPARATOR.'venv'.DIRECTORY_SEPARATOR.'Scripts'.DIRECTORY_SEPARATOR.'python.exe'),
        ];

        foreach ($candidates as $executable) {
            if (! file_exists($executable)) {
                continue;
            }

            $version = $this->capture([$executable, '--version'], 10);
            if ($version === null) {
                continue;
            }

            // "Python 3.11.9" -> "3.11.9"
            if (preg_match('/(\d+\.\d+(?:\.\d+)?)/', $version, $m) !== 1) {
                continue;
            }

            $normalized = $m[1];

            return [
                'label' => 'Python '.$normalized,
                'ok' => true,
                'detail' => $version,
                'minimum' => version_compare($normalized, self::PYTHON_MINIMUM, '<'),
            ];
        }

        return [
            'label' => 'Python tidak ditemukan',
            'ok' => false,
            'detail' => 'venv python_engine\venv\Scripts\python.exe belum tersedia.',
            'minimum' => true,
        ];
    }

    /**
     * @return array{label: string, ok: bool}
     */
    private function probeTypst(): array
    {
        $executable = base_path('typst_engine'.DIRECTORY_SEPARATOR.'bin'.DIRECTORY_SEPARATOR.'typst.exe');

        if (! file_exists($executable)) {
            return ['label' => 'Typst tidak ditemukan', 'ok' => false];
        }

        $version = $this->capture([$executable, '--version'], 10);
        if ($version === null || preg_match('/(\d+\.\d+(?:\.\d+)?)/', $version, $m) !== 1) {
            return ['label' => 'Typst (versi tidak terbaca)', 'ok' => false];
        }

        return ['label' => 'Typst '.$m[1], 'ok' => true];
    }

    /**
     * Deteksi kesiapan offline: seluruh view harus bebas referensi CDN eksternal.
     * Pemeriksaan ringan pada layout dan partial logo yang dipakai di mana saja.
     */
    private function hasExternalAssetReference(): bool
    {
        $candidates = [
            base_path('resources'.DIRECTORY_SEPARATOR.'views'.DIRECTORY_SEPARATOR.'layouts'.DIRECTORY_SEPARATOR.'app.blade.php'),
            base_path('resources'.DIRECTORY_SEPARATOR.'views'.DIRECTORY_SEPARATOR.'partials'.DIRECTORY_SEPARATOR.'bps-logo.blade.php'),
        ];

        foreach ($candidates as $file) {
            if (! is_file($file)) {
                continue;
            }

            $contents = (string) file_get_contents($file);
            if (preg_match('#(src|href)\s*=\s*["\']https?://#i', $contents) === 1) {
                return false;
            }
        }

        return true;
    }

    /**
     * Jalankan perintah biner dan kembalikan trimmed stdout, atau null bila gagal.
     *
     * @param  array<int, string>  $command
     */
    private function capture(array $command, int $timeoutSeconds): ?string
    {
        try {
            $process = new Process($command, base_path());
            $process->setTimeout($timeoutSeconds);
            $process->run();

            if (! $process->isSuccessful()) {
                return null;
            }

            $output = trim($process->getOutput());

            return $output === '' ? null : $output;
        } catch (\Throwable) {
            // Probe bersifat kosmetik: kegagalan tidak boleh menjatuhkan halaman.
            return null;
        }
    }
}

<?php

namespace App\Services;

use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Exception;

class PythonWorkerService
{
    protected string $pythonPath;

    public function __construct()
    {
        // Safe Windows 11 path resolution
        $this->pythonPath = base_path('python_engine' . DIRECTORY_SEPARATOR . 'venv' . DIRECTORY_SEPARATOR . 'Scripts' . DIRECTORY_SEPARATOR . 'python.exe');
        if (!file_exists($this->pythonPath)) {
            // Fallback to laragon global python if venv not ready
            $this->pythonPath = 'C:\\laragon\\bin\\python\\python-3.10\\python.exe';
        }
    }

    /**
     * Eksekusi skrip analitis Python dan parse respon JSON dari stdout.
     */
    public function execute(string $scriptPath, array $args = [], int $timeout = 60): array
    {
        $fullScriptPath = base_path(str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $scriptPath));
        if (!file_exists($fullScriptPath)) {
            throw new Exception("Skrip Python tidak ditemukan di: {$fullScriptPath}");
        }

        // Preserve FULL parent environment (Windows needs SystemDrive, TEMP, COMSPEC, PATHEXT, OS, etc.)
        // Previous array_merge($_SERVER, [...overrides]) dropped critical vars causing
        // "RuntimeError: Could not determine home directory" under `php artisan serve`.
        $serverEnv = array_merge($_ENV ?? [], $_SERVER ?? []);
        // getenv() associative array as fallback source
        if (function_exists('getenv')) {
            foreach (['PATH','PATHEXT','SYSTEMROOT','WINDIR','SYSTEMDRIVE','COMSPEC','OS','TEMP','TMP','USERPROFILE','HOMEDRIVE','HOMEPATH','HOME','USERNAME','LANG','SYSTEMENCODING'] as $k) {
                $v = getenv($k);
                if ($v !== false && $v !== '' && !isset($serverEnv[$k])) {
                    $serverEnv[$k] = $v;
                }
            }
        }
        $homeDrive = $serverEnv['HOMEDRIVE'] ?? 'C:';
        $homePath = $serverEnv['HOMEPATH'] ?? '\\Users\\Default';
        $userProfile = $serverEnv['USERPROFILE'] ?? ($homeDrive . $homePath);
        if (empty($userProfile)) {
            $userProfile = 'C:\\Windows\\Temp';
        }
        $systemRoot = $serverEnv['SYSTEMROOT'] ?? $serverEnv['WINDIR'] ?? 'C:\\Windows';
        $tmpDir = $serverEnv['TEMP'] ?? $serverEnv['TMP'] ?? storage_path('temp');
        $env = array_merge($serverEnv, [
            'SYSTEMROOT' => $systemRoot,
            'WINDIR' => $serverEnv['WINDIR'] ?? $systemRoot,
            'SYSTEMDRIVE' => $serverEnv['SYSTEMDRIVE'] ?? 'C:',
            'COMSPEC' => $serverEnv['COMSPEC'] ?? $systemRoot . '\\system32\\cmd.exe',
            'OS' => $serverEnv['OS'] ?? 'Windows_NT',
            'PATH' => $serverEnv['PATH'] ?? ($systemRoot . '\\system32;' . $systemRoot),
            'PATHEXT' => $serverEnv['PATHEXT'] ?? '.COM;.EXE;.BAT;.CMD;.VBS;.JS;.WS;.MSC;.PY;.PYW',
            'TEMP' => $tmpDir,
            'TMP' => $tmpDir,
            'USERPROFILE' => $userProfile,
            'HOMEDRIVE' => $serverEnv['HOMEDRIVE'] ?? 'C:',
            'HOMEPATH' => $serverEnv['HOMEPATH'] ?? '\\Users\\Default',
            'HOME' => $serverEnv['HOME'] ?? $userProfile,
            'MPLCONFIGDIR' => storage_path('temp'),
            'PYTHONHASHSEED' => '0',
            'PYTHONIOENCODING' => 'utf-8',
        ]);

        $command = array_merge([$this->pythonPath, $fullScriptPath], $args);
        $process = new Process($command, base_path(), $env);
        $process->setTimeout($timeout);

        $process->run();

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        $output = trim($process->getOutput());
        
        // Strip any matplotlib font building warning if present
        if (str_contains($output, '{') && !str_starts_with($output, '{')) {
            $jsonStart = strpos($output, '{');
            $output = substr($output, $jsonStart);
        }

        $decoded = json_decode($output, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return [
                'status' => 'success',
                'raw_output' => $output,
            ];
        }

        return $decoded;
    }

    /**
     * Membersihkan berkas Excel mentah OPD
     */
    public function cleanExcel(string $excelPath, string|int $sheet = 0): array
    {
        return $this->execute('python_engine/parsers/generic_cleaner.py', [$excelPath, '--sheet', (string)$sheet]);
    }

    /**
     * Agregasi data individu Dapodik/EMIS
     */
    public function aggregateSchools(string $excelPath, string $level = 'kecamatan'): array
    {
        $level = strtolower(trim($level)) === 'desa' ? 'desa' : 'kecamatan';
        return $this->execute('python_engine/parsers/individual_aggregator.py', [$excelPath, '--level', $level]);
    }

    /**
     * Kalkulasi Matriks Survei Kebutuhan Data (SKD) & Diagram Kartesius
     */
    public function runSkdEngine(?string $filePath = null, ?string $outputSvg = null): array
    {
        $args = [];
        if ($filePath) {
            $args[] = '--file';
            $args[] = $filePath;
        }
        if ($outputSvg) {
            $args[] = '--output-svg';
            $args[] = $outputSvg;
        }
        return $this->execute('python_engine/calculators/skd_engine.py', $args);
    }

    /**
     * Render grafik piramida penduduk
     */
    public function renderPopulationPyramid(string $outputSvg, string $district = 'Kabupaten Jember', int $year = 2026): array
    {
        return $this->execute('python_engine/visualizers/population_pyramid.py', [
            '--output', $outputSvg,
            '--district', $district,
            '--year', (string)$year,
        ]);
    }

    /**
     * Render grafik curah hujan bulanan
     */
    public function renderClimateChart(string $outputSvg, string $district = 'Kabupaten Jember', int $year = 2026): array
    {
        return $this->execute('python_engine/visualizers/climate_chart.py', [
            '--output', $outputSvg,
            '--district', $district,
            '--year', (string)$year,
        ]);
    }
}

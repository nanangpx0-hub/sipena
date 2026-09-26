<?php

namespace App\Services;

use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Exception;

class TypstCompilerService
{
    protected string $typstBinary;

    public function __construct()
    {
        $this->typstBinary = base_path('typst_engine' . DIRECTORY_SEPARATOR . 'bin' . DIRECTORY_SEPARATOR . 'typst.exe');
        if (!file_exists($this->typstBinary)) {
            throw new Exception("Typst binary tidak ditemukan di: {$this->typstBinary}");
        }
    }

    /**
     * Kompilasi berkas markup .typ menjadi berkas PDF standar cetak (300 DPI).
     */
    public function compile(string $inputTypPath, string $outputPdfPath, int $timeout = 120): array
    {
        $fullInput = base_path(str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $inputTypPath));
        $fullOutput = base_path(str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $outputPdfPath));

        if (!file_exists($fullInput)) {
            throw new Exception("Berkas input Typst tidak ditemukan: {$fullInput}");
        }

        // Ensure target directory exists
        $outputDir = dirname($fullOutput);
        if (!is_dir($outputDir)) {
            mkdir($outputDir, 0755, true);
        }

        $command = [
            $this->typstBinary,
            'compile',
            '--font-path', 'C:\\Windows\\Fonts',
            '--root', base_path(),
            $fullInput,
            $fullOutput,
        ];

        $process = new Process($command);
        $process->setTimeout($timeout);
        $process->setWorkingDirectory(base_path());

        $process->run();

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        if (!file_exists($fullOutput)) {
            throw new Exception("Gagal menghasilkan berkas output PDF di: {$fullOutput}");
        }

        return [
            'status' => 'success',
            'input' => $inputTypPath,
            'output_pdf' => $outputPdfPath,
            'file_size_bytes' => filesize($fullOutput),
        ];
    }

    /**
     * Dapatkan versi compiler Typst
     */
    public function getVersion(): string
    {
        $process = new Process([$this->typstBinary, '--version']);
        $process->run();
        return trim($process->getOutput());
    }
}

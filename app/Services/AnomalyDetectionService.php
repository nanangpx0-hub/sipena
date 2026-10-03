<?php

namespace App\Services;

/**
 * Deteksi anomali data OPD hasil ingest.
 *
 * Membandingkan tabel & bab yang sama pada tahun sebelumnya (t-1) untuk
 * menandai lonjakan/penurunan ekstrem (>= 50%) maupun nilai negatif, sehingga
 * meja kerja Approver dapat menyorotinya sebelum Approve & Lock.
 */
class AnomalyDetectionService
{
    /** Ambang lonjakan/penurunan ekstrem dalam persen. */
    public const SPIKE_THRESHOLD = 50.0;

    /** Batas jumlah peringatan yang disimpan agar metadata tabel tetap ringkas. */
    public const MAX_WARNINGS = 12;

    /**
     * @param  array<string, mixed>  $current  table_data hasil ekstraksi terbaru
     * @param  array<string, mixed>|null  $previous  table_data tahun sebelumnya (null bila belum ada)
     * @return array<int, string>
     */
    public function detect(array $current, ?array $previous): array
    {
        $headers = $this->headers($current);
        $rows = $this->rows($current);

        $warnings = $this->collectNegatives($rows, $headers);
        $overflow = 0;

        if ($previous !== null) {
            $previousRows = $this->rows($previous);

            if ($previousRows !== []) {
                foreach ($this->align($rows, $previousRows, $headers) as [$row, $oldRow]) {
                    foreach ($headers as $header) {
                        $newValue = $this->number($row[$header] ?? null);
                        $oldValue = $this->number($oldRow[$header] ?? null);
                        if ($newValue === null || $oldValue === null) {
                            continue;
                        }

                        $warning = $this->spikeMessage(
                            sprintf('Baris kolom "%s"', $header),
                            $oldValue,
                            $newValue
                        );

                        if ($warning === null) {
                            continue;
                        }

                        if (count($warnings) >= self::MAX_WARNINGS) {
                            $overflow++;

                            continue;
                        }

                        $warnings[] = $warning;
                    }
                }
            }

            $totalWarning = $this->spikeMessage(
                'Total angka tabel',
                $this->sumAll($rows, $headers),
                $this->sumAll($previousRows, $headers)
            );

            if ($totalWarning !== null && count($warnings) < self::MAX_WARNINGS) {
                $warnings[] = $totalWarning;
            } elseif ($totalWarning !== null) {
                $overflow++;
            }
        }

        if ($overflow > 0) {
            $warnings[] = "...dan {$overflow} anomali lainnya tidak ditampilkan.";
        }

        return $warnings;
    }

    /**
     * Nilai negatif pada data baru selalu ditandai (tidak wajib ada pembanding).
     *
     * @return array<int, string>
     */
    protected function collectNegatives(array $rows, array $headers): array
    {
        $warnings = [];
        $overflow = 0;

        foreach ($rows as $index => $row) {
            foreach ($headers as $header) {
                $value = $this->number($row[$header] ?? null);
                if ($value === null || $value >= 0) {
                    continue;
                }

                if (count($warnings) >= self::MAX_WARNINGS) {
                    $overflow++;

                    continue;
                }

                $warnings[] = sprintf(
                    'Baris %d kolom "%s": nilai negatif (%s).',
                    $index + 1,
                    $header,
                    $this->format($value)
                );
            }
        }

        if ($overflow > 0) {
            $warnings[] = "...dan {$overflow} nilai negatif lainnya tidak ditampilkan.";
        }

        return $warnings;
    }

    /**
     * Susun pesan lonjakan bila perubahan mencapai ambang 50% (dari 0 ikut ditandai).
     */
    protected function spikeMessage(string $label, float $oldValue, float $newValue): ?string
    {
        if ($oldValue == 0.0) {
            return $newValue == 0.0
                ? null
                : sprintf('%s: 0 menjadi %s (lonjakan tak terhingga).', $label, $this->format($newValue));
        }

        $change = (($newValue - $oldValue) / abs($oldValue)) * 100;
        if (abs($change) < self::SPIKE_THRESHOLD) {
            return null;
        }

        return sprintf(
            '%s: %s menjadi %s (%+.1f%%).',
            $label,
            $this->format($oldValue),
            $this->format($newValue),
            $change
        );
    }

    /**
     * Pasangkan baris tabel baru dengan tabel tahun sebelumnya: cocokkan lewat
     * kolom label bila memungkinkan, selain itu pakai urutan baris.
     *
     * @return array<int, array{0: array<string, mixed>, 1: array<string, mixed>}>
     */
    protected function align(array $rows, array $previousRows, array $headers): array
    {
        $labelColumn = $this->detectLabelColumn($rows, $headers)
            ?? $this->detectLabelColumn($previousRows, $headers);

        if ($labelColumn === null) {
            $count = min(count($rows), count($previousRows));
            $pairs = [];
            for ($i = 0; $i < $count; $i++) {
                $pairs[] = [$rows[$i], $previousRows[$i]];
            }

            return $pairs;
        }

        $previousByKey = [];
        foreach ($previousRows as $previousRow) {
            $key = $this->rowKey($previousRow[$labelColumn] ?? null);
            if ($key !== null) {
                $previousByKey[$key] = $previousRow;
            }
        }

        $pairs = [];
        foreach ($rows as $index => $row) {
            $key = $this->rowKey($row[$labelColumn] ?? null);
            $match = ($key !== null && isset($previousByKey[$key]))
                ? $previousByKey[$key]
                : ($previousRows[$index] ?? null);

            if ($match !== null) {
                $pairs[] = [$row, $match];
            }
        }

        return $pairs;
    }

    /**
     * Kolom label = kolom teks yang mayoritas berisi bukan angka.
     */
    protected function detectLabelColumn(array $rows, array $headers): ?string
    {
        foreach ($headers as $header) {
            $text = 0;
            $total = 0;
            foreach ($rows as $row) {
                $value = $row[$header] ?? null;
                if ($value === null || $value === '') {
                    continue;
                }
                $total++;
                if ($this->number($value) === null && is_string($value)) {
                    $text++;
                }
            }

            if ($total > 0 && $text / $total >= 0.6) {
                return $header;
            }
        }

        return null;
    }

    protected function rowKey(mixed $value): ?string
    {
        if (! is_string($value) || trim($value) === '') {
            return null;
        }

        return strtolower(trim($value));
    }

    /**
     * @return array<int, string>
     */
    protected function headers(array $data): array
    {
        $headers = array_values(array_filter((array) ($data['headers'] ?? []), 'is_scalar'));
        if ($headers !== []) {
            return array_map('strval', $headers);
        }

        $rows = $this->rows($data);
        $union = [];
        foreach ($rows as $row) {
            foreach (array_keys($row) as $key) {
                $union[(string) $key] = true;
            }
        }

        return array_keys($union);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function rows(array $data): array
    {
        $rows = is_array($data['data'] ?? null) ? array_values($data['data']) : [];

        return array_values(array_filter($rows, 'is_array'));
    }

    protected function sumAll(array $rows, array $headers): float
    {
        $sum = 0.0;
        foreach ($rows as $row) {
            foreach ($headers as $header) {
                $value = $this->number($row[$header] ?? null);
                if ($value !== null) {
                    $sum += $value;
                }
            }
        }

        return $sum;
    }

    protected function number(mixed $value): ?float
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
            return (float) str_replace([',', '.'], ['', ''], $raw);
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

    protected function format(float $value): string
    {
        $rounded = round($value, 2);

        if (abs($rounded - round($rounded)) < 0.001) {
            return number_format(round($rounded), 0, ',', '.');
        }

        return number_format($rounded, 2, ',', '.');
    }
}

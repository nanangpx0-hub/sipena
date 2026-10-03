<?php

namespace App\Services;

use App\Models\District;
use App\Models\Village;
use Illuminate\Support\Collection;

class FuzzyMatchService
{
    protected float $similarityThreshold;

    /** Cache master desa per kecamatan agar unggah banyak baris tetap ringan. */
    protected array $villageCache = [];

    public function __construct(float $similarityThreshold = 0.85)
    {
        $this->similarityThreshold = $similarityThreshold;
    }

    /**
     * Hitung persentase kemiripan antara dua string (0.0 sampai 1.0)
     */
    public function calculateSimilarity(string $str1, string $str2): float
    {
        $s1 = strtolower(trim($str1));
        $s2 = strtolower(trim($str2));

        if ($s1 === $s2) {
            return 1.0;
        }

        $len1 = strlen($s1);
        $len2 = strlen($s2);

        if ($len1 === 0 || $len2 === 0) {
            return 0.0;
        }

        $lev = levenshtein($s1, $s2);
        $maxLen = max($len1, $len2);

        return 1.0 - ($lev / $maxLen);
    }

    /**
     * Cocokkan nama kecamatan kotor dari dinas ke master Kecamatan BPS Jember
     */
    public function matchDistrict(string $rawName): ?array
    {
        $districts = District::all();
        $bestMatch = null;
        $highestSimilarity = 0.0;

        foreach ($districts as $district) {
            $sim = $this->calculateSimilarity($rawName, $district->name);
            if ($sim > $highestSimilarity) {
                $highestSimilarity = $sim;
                $bestMatch = $district;
            }
        }

        if ($highestSimilarity >= $this->similarityThreshold && $bestMatch) {
            return [
                'matched' => true,
                'district_id' => $bestMatch->id,
                'bps_code' => $bestMatch->bps_code,
                'official_name' => $bestMatch->name,
                'raw_name' => $rawName,
                'similarity' => round($highestSimilarity * 100, 2),
            ];
        }

        return [
            'matched' => false,
            'raw_name' => $rawName,
            'closest_candidate' => $bestMatch?->name,
            'similarity' => round($highestSimilarity * 100, 2),
        ];
    }

    /**
     * Cocokkan nama desa kotor ke master Desa BPS pada suatu kecamatan
     */
    public function matchVillage(string $rawName, int $districtId): ?array
    {
        $villages = Village::where('district_id', $districtId)->get();
        $bestMatch = null;
        $highestSimilarity = 0.0;

        foreach ($villages as $village) {
            $sim = $this->calculateSimilarity($rawName, $village->name);
            if ($sim > $highestSimilarity) {
                $highestSimilarity = $sim;
                $bestMatch = $village;
            }
        }

        if ($highestSimilarity >= $this->similarityThreshold && $bestMatch) {
            return [
                'matched' => true,
                'village_id' => $bestMatch->id,
                'bps_code' => $bestMatch->bps_code,
                'official_name' => $bestMatch->name,
                'raw_name' => $rawName,
                'similarity' => round($highestSimilarity * 100, 2),
            ];
        }

        return [
            'matched' => false,
            'raw_name' => $rawName,
            'closest_candidate' => $bestMatch?->name,
            'similarity' => round($highestSimilarity * 100, 2),
        ];
    }

    /**
     * Cari kolom nama desa pada header tabel hasil ekstraksi.
     *
     * @param  array<int, string>  $headers
     */
    public function villageColumn(array $headers): ?string
    {
        foreach ($headers as $header) {
            if (preg_match('/\bnama desa\b|\bdesa\b|\bkelurahan\b|\bds\b/i', trim((string) $header))) {
                return (string) $header;
            }
        }

        return null;
    }

    /**
     * Saran koreksi baku nama desa untuk satu nilai sel ingesti KDA.
     *
     * Pemicu: kemiripan 75%-95% (typo/singkatan) atau nama asli yang setelah
     * normalisasi awalan ("Ds.", "Kel.", "Dsn.", ...) persis sama dengan nama
     * baku namun berbeda ejaannya.
     *
     * @return array{raw_name: string, official_name: string, bps_code: string|null, similarity: float, reason: string}|null
     */
    public function suggestVillage(string $rawName, int $districtId, float $min = 75.0, float $max = 95.0): ?array
    {
        $raw = trim($rawName);
        if ($raw === '') {
            return null;
        }

        $villages = $this->villagesFor($districtId);
        if ($villages->isEmpty()) {
            return null;
        }

        $normalized = $this->normalizeVillageName($raw);
        if ($normalized === '') {
            return null;
        }

        $best = null;
        $highestSimilarity = 0.0;

        foreach ($villages as $village) {
            $similarity = max(
                $this->calculateSimilarity($raw, $village->name),
                $this->calculateSimilarity($normalized, $village->name),
            );

            if ($similarity > $highestSimilarity) {
                $highestSimilarity = $similarity;
                $best = $village;
            }
        }

        if (! $best || strtolower($raw) === strtolower($best->name)) {
            return null;
        }

        $percentage = round($highestSimilarity * 100, 2);
        $inReviewBand = $percentage >= $min && $percentage <= $max;
        $prefixOnly = $highestSimilarity >= 0.999
            && $normalized === strtolower($best->name);

        if (! $inReviewBand && ! $prefixOnly) {
            return null;
        }

        return [
            'raw_name' => $raw,
            'official_name' => $best->name,
            'bps_code' => $best->bps_code,
            'similarity' => $percentage,
            'reason' => $prefixOnly && ! $inReviewBand
                ? 'Berbeda awalan/singkatan (mis. Ds./ Kel.)'
                : 'Kemiripan typo/singkatan nama desa',
        ];
    }

    /**
     * Normalisasi nama desa: huruf kecil, tanpa tanda baca, tanpa satu awalan
     * "Desa", "Kel.", "Dsn.", "Kp.", dst. (mis. "Ds. Cakru" -> "cakru").
     */
    public function normalizeVillageName(string $rawName): string
    {
        $value = strtolower(trim($rawName));
        $value = preg_replace('/[^a-z0-9\s]/', ' ', $value) ?? '';
        $value = preg_replace('/\s+/', ' ', $value) ?? '';
        $value = preg_replace('/^(desa|kelurahan|dusun|kampung|ds|dsn|kel|kp)\s+/', '', $value) ?? $value;

        return trim($value);
    }

    /**
     * @return Collection<int, Village>
     */
    protected function villagesFor(int $districtId)
    {
        if (! array_key_exists($districtId, $this->villageCache)) {
            $this->villageCache[$districtId] = Village::where('district_id', $districtId)
                ->orderBy('name')
                ->get();
        }

        return $this->villageCache[$districtId];
    }
}

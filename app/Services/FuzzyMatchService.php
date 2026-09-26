<?php

namespace App\Services;

use App\Models\District;
use App\Models\Village;

class FuzzyMatchService
{
    protected float $similarityThreshold;

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
}

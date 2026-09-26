<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DistrictSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $districts = [
            ['bps_code' => '3509010', 'name' => 'Kencong', 'capital_city' => 'Kencong', 'altitude_min' => 7, 'altitude_max' => 15, 'total_area_sqkm' => 61.70],
            ['bps_code' => '3509020', 'name' => 'Gumukmas', 'capital_city' => 'Gumukmas', 'altitude_min' => 5, 'altitude_max' => 12, 'total_area_sqkm' => 93.30],
            ['bps_code' => '3509030', 'name' => 'Puger', 'capital_city' => 'Puger Kulon', 'altitude_min' => 0, 'altitude_max' => 10, 'total_area_sqkm' => 163.67],
            ['bps_code' => '3509040', 'name' => 'Wuluhan', 'capital_city' => 'Dukuhdempok', 'altitude_min' => 5, 'altitude_max' => 20, 'total_area_sqkm' => 128.53],
            ['bps_code' => '3509050', 'name' => 'Ambulu', 'capital_city' => 'Ambulu', 'altitude_min' => 8, 'altitude_max' => 30, 'total_area_sqkm' => 116.50],
            ['bps_code' => '3509060', 'name' => 'Tempurejo', 'capital_city' => 'Tempurejo', 'altitude_min' => 20, 'altitude_max' => 250, 'total_area_sqkm' => 524.46],
            ['bps_code' => '3509070', 'name' => 'Silo', 'capital_city' => 'Sempolan', 'altitude_min' => 250, 'altitude_max' => 850, 'total_area_sqkm' => 330.43],
            ['bps_code' => '3509080', 'name' => 'Mayang', 'capital_city' => 'Tegalwaru', 'altitude_min' => 180, 'altitude_max' => 320, 'total_area_sqkm' => 56.08],
            ['bps_code' => '3509090', 'name' => 'Mumbulsari', 'capital_city' => 'Mumbulsari', 'altitude_min' => 70, 'altitude_max' => 180, 'total_area_sqkm' => 87.99],
            ['bps_code' => '3509100', 'name' => 'Jenggawah', 'capital_city' => 'Wonojati', 'altitude_min' => 30, 'altitude_max' => 110, 'total_area_sqkm' => 53.64],
            ['bps_code' => '3509101', 'name' => 'Ajung', 'capital_city' => 'Klompangan', 'altitude_min' => 60, 'altitude_max' => 120, 'total_area_sqkm' => 60.10],
            ['bps_code' => '3509110', 'name' => 'Rambipuji', 'capital_city' => 'Rambipuji', 'altitude_min' => 45, 'altitude_max' => 95, 'total_area_sqkm' => 52.80],
            ['bps_code' => '3509120', 'name' => 'Balung', 'capital_city' => 'Balung Lor', 'altitude_min' => 20, 'altitude_max' => 50, 'total_area_sqkm' => 48.00],
            ['bps_code' => '3509130', 'name' => 'Umbulsari', 'capital_city' => 'Umbulsari', 'altitude_min' => 18, 'altitude_max' => 45, 'total_area_sqkm' => 70.83],
            ['bps_code' => '3509140', 'name' => 'Semboro', 'capital_city' => 'Semboro', 'altitude_min' => 25, 'altitude_max' => 50, 'total_area_sqkm' => 46.00],
            ['bps_code' => '3509150', 'name' => 'Jombang', 'capital_city' => 'Jombang', 'altitude_min' => 15, 'altitude_max' => 35, 'total_area_sqkm' => 54.30],
            ['bps_code' => '3509160', 'name' => 'Sumberbaru', 'capital_city' => 'Yosorati', 'altitude_min' => 35, 'altitude_max' => 400, 'total_area_sqkm' => 156.40],
            ['bps_code' => '3509170', 'name' => 'Tanggul', 'capital_city' => 'Tanggul Kulon', 'altitude_min' => 40, 'altitude_max' => 200, 'total_area_sqkm' => 207.72],
            ['bps_code' => '3509180', 'name' => 'Bangsalsari', 'capital_city' => 'Bangsalsari', 'altitude_min' => 45, 'altitude_max' => 250, 'total_area_sqkm' => 160.71],
            ['bps_code' => '3509190', 'name' => 'Panti', 'capital_city' => 'Panti', 'altitude_min' => 80, 'altitude_max' => 550, 'total_area_sqkm' => 162.34],
            ['bps_code' => '3509191', 'name' => 'Sukorambi', 'capital_city' => 'Sukorambi', 'altitude_min' => 120, 'altitude_max' => 450, 'total_area_sqkm' => 57.30],
            ['bps_code' => '3509200', 'name' => 'Arjasa', 'capital_city' => 'Arjasa', 'altitude_min' => 120, 'altitude_max' => 400, 'total_area_sqkm' => 37.05],
            ['bps_code' => '3509201', 'name' => 'Pakusari', 'capital_city' => 'Pakusari', 'altitude_min' => 130, 'altitude_max' => 250, 'total_area_sqkm' => 29.11],
            ['bps_code' => '3509210', 'name' => 'Kalisat', 'capital_city' => 'Glagahwero', 'altitude_min' => 200, 'altitude_max' => 380, 'total_area_sqkm' => 53.48],
            ['bps_code' => '3509220', 'name' => 'Ledokombo', 'capital_city' => 'Sumberlesung', 'altitude_min' => 280, 'altitude_max' => 700, 'total_area_sqkm' => 138.89],
            ['bps_code' => '3509230', 'name' => 'Sumberjambe', 'capital_city' => 'Sumberjambe', 'altitude_min' => 350, 'altitude_max' => 900, 'total_area_sqkm' => 131.27],
            ['bps_code' => '3509240', 'name' => 'Sukowono', 'capital_city' => 'Sukowono', 'altitude_min' => 250, 'altitude_max' => 450, 'total_area_sqkm' => 45.45],
            ['bps_code' => '3509250', 'name' => 'Jelbuk', 'capital_city' => 'Jelbuk', 'altitude_min' => 200, 'altitude_max' => 600, 'total_area_sqkm' => 71.05],
            ['bps_code' => '3509710', 'name' => 'Kaliwates', 'capital_city' => 'Kaliwates', 'altitude_min' => 85, 'altitude_max' => 140, 'total_area_sqkm' => 25.04],
            ['bps_code' => '3509720', 'name' => 'Sumbersari', 'capital_city' => 'Karangrejo', 'altitude_min' => 100, 'altitude_max' => 165, 'total_area_sqkm' => 35.79],
            ['bps_code' => '3509730', 'name' => 'Patrang', 'capital_city' => 'Patrang', 'altitude_min' => 110, 'altitude_max' => 250, 'total_area_sqkm' => 37.01],
        ];

        foreach ($districts as $d) {
            $districtId = DB::table('districts')->insertGetId([
                'bps_code' => $d['bps_code'],
                'name' => $d['name'],
                'capital_city' => $d['capital_city'],
                'altitude_min' => $d['altitude_min'],
                'altitude_max' => $d['altitude_max'],
                'total_area_sqkm' => $d['total_area_sqkm'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Add sample villages for demonstration & fuzzy matching
            $sampleVillages = [
                ['bps_code' => $d['bps_code'] . '001', 'name' => $d['name'] . ' Kota', 'is_kelurahan' => str_starts_with($d['bps_code'], '35097'), 'area_sqm' => 12500000],
                ['bps_code' => $d['bps_code'] . '002', 'name' => $d['name'] . ' Timur', 'is_kelurahan' => false, 'area_sqm' => 14200000],
                ['bps_code' => $d['bps_code'] . '003', 'name' => $d['name'] . ' Barat', 'is_kelurahan' => false, 'area_sqm' => 11800000],
            ];

            foreach ($sampleVillages as $v) {
                DB::table('villages')->insert([
                    'district_id' => $districtId,
                    'bps_code' => $v['bps_code'],
                    'name' => $v['name'],
                    'is_kelurahan' => $v['is_kelurahan'],
                    'area_sqm' => $v['area_sqm'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}

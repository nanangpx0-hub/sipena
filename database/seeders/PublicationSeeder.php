<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PublicationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $year = 2026;
        $softDeadline = now()->addDays(14);
        $hardDeadline = now()->addDays(30);

        // 1. DDA Publikasi Induk
        $ddaId = DB::table('publications')->insertGetId([
            'type' => 'DDA',
            'district_id' => null,
            'year' => $year,
            'title' => 'Kabupaten Jember Dalam Angka '.$year,
            'catalog_number' => '1102001.3509',
            'publication_number' => '35090.2601',
            'issn' => '0215-2231',
            'volume' => '54',
            'book_size' => 'A5',
            'status' => 'PENDING_DATA',
            'soft_deadline' => $softDeadline,
            'hard_deadline' => $hardDeadline,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 2. SKD Publikasi Analisis
        $skdId = DB::table('publications')->insertGetId([
            'type' => 'SKD',
            'district_id' => null,
            'year' => $year,
            'title' => 'Analisis Hasil Survei Kebutuhan Data BPS Kabupaten Jember '.$year,
            'catalog_number' => '1305011.3509',
            'publication_number' => '35090.2602',
            'issn' => '2548-8120',
            'volume' => '9',
            'book_size' => '18,2 x 25,7',
            'status' => 'PENDING_DATA',
            'soft_deadline' => $softDeadline->copy()->addDays(7),
            'hard_deadline' => $hardDeadline->copy()->addDays(7),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 3. 31 KDA Kecamatan
        $districts = DB::table('districts')->orderBy('bps_code')->get();
        foreach ($districts as $d) {
            $kdaId = DB::table('publications')->insertGetId([
                'type' => 'KDA',
                'district_id' => $d->id,
                'year' => $year,
                'title' => 'Kecamatan '.$d->name.' Dalam Angka '.$year,
                'catalog_number' => '1102001.3509'.substr($d->bps_code, 4),
                'publication_number' => '35090.26'.substr($d->bps_code, 4),
                'issn' => '2828-'.rand(1000, 9999),
                'volume' => '18',
                'book_size' => 'A5',
                'status' => 'PENDING_DATA',
                'soft_deadline' => $softDeadline->copy()->addDays(14),
                'hard_deadline' => $hardDeadline->copy()->addDays(14),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Add standard 7 chapters for KDA
            $chapters = [
                ['num' => 1, 'id' => 'Geografi dan Iklim', 'en' => 'Geography and Climate', 'hl' => 'Luas Wilayah', 'val' => $d->total_area_sqkm.' km²'],
                ['num' => 2, 'id' => 'Pemerintahan', 'en' => 'Government', 'hl' => 'Jumlah Desa/Kelurahan', 'val' => '10 Desa'],
                ['num' => 3, 'id' => 'Penduduk', 'en' => 'Population', 'hl' => 'Jumlah Penduduk', 'val' => '72.450 Jiwa'],
                ['num' => 4, 'id' => 'Sosial dan Kesejahteraan Rakyat', 'en' => 'Social and Welfare', 'hl' => 'Jumlah Sekolah Dasar', 'val' => '34 Unit'],
                ['num' => 5, 'id' => 'Pertanian', 'en' => 'Agriculture', 'hl' => 'Produksi Padi', 'val' => '48.210 Ton'],
                ['num' => 6, 'id' => 'Pariwisata, Transportasi, dan Komunikasi', 'en' => 'Tourism and Transportation', 'hl' => 'Objek Wisata', 'val' => '6 Lokasi'],
                ['num' => 7, 'id' => 'Perbankan, Koperasi, dan Perdagangan', 'en' => 'Banking, Cooperatives, and Trade', 'hl' => 'Jumlah Koperasi Aktif', 'val' => '14 Koperasi'],
            ];

            foreach ($chapters as $ch) {
                DB::table('chapter_narratives')->insert([
                    'publication_id' => $kdaId,
                    'chapter_number' => $ch['num'],
                    'title_id' => $ch['id'],
                    'title_en' => $ch['en'],
                    'narrative_id' => 'Kecamatan '.$d->name.' terletak pada ketinggian rata-rata '.$d->altitude_min.'-'.$d->altitude_max.' meter di atas permukaan laut dengan ibukota kecamatan berada di '.$d->capital_city.'. Pada tahun '.$year.', luas wilayah tercatat sebesar '.$d->total_area_sqkm.' km².',
                    'narrative_en' => $d->name.' Subdistrict is located at an average altitude of '.$d->altitude_min.'-'.$d->altitude_max.' meters above sea level with capital located in '.$d->capital_city.'. In '.$year.', total area was recorded at '.$d->total_area_sqkm.' sq.km.',
                    'highlight_label' => $ch['hl'],
                    'highlight_value' => $ch['val'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}

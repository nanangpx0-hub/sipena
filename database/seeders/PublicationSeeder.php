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

        $ddaChapters = [
            ['num' => 1, 'id' => 'Geografi dan Iklim', 'en' => 'Geography and Climate', 'hl' => 'Luas Wilayah Jember', 'val' => '3.306,68 km²'],
            ['num' => 2, 'id' => 'Pemerintahan', 'en' => 'Government', 'hl' => 'Jumlah Kecamatan', 'val' => '31 Kecamatan'],
            ['num' => 3, 'id' => 'Penduduk dan Ketenagakerjaan', 'en' => 'Population and Employment', 'hl' => 'Jumlah Penduduk Jember', 'val' => '2.564.890 Jiwa'],
            ['num' => 4, 'id' => 'Sosial dan Kesejahteraan Rakyat', 'en' => 'Social and Welfare', 'hl' => 'Indeks Pembangunan Manusia (IPM)', 'val' => '69,12 Poin'],
            ['num' => 5, 'id' => 'Pertanian, Kehutanan, Peternakan, dan Perikanan', 'en' => 'Agriculture, Forestry, Animal Husbandry, and Fisheries', 'hl' => 'Produksi Padi Kabupaten', 'val' => '620.450 Ton'],
            ['num' => 6, 'id' => 'Pertambangan dan Energi', 'en' => 'Mining and Energy', 'hl' => 'Pelanggan Listrik PLN', 'val' => '780.200 Pelanggan'],
            ['num' => 7, 'id' => 'Industri Pengolahan', 'en' => 'Manufacturing', 'hl' => 'Unit Industri Mikro Kecil', 'val' => '42.150 Unit'],
            ['num' => 8, 'id' => 'Konstruksi', 'en' => 'Construction', 'hl' => 'Panjang Jalan Kabupaten', 'val' => '2.845,60 km'],
            ['num' => 9, 'id' => 'Perdagangan, Hotel, dan Pariwisata', 'en' => 'Trade, Hotel, and Tourism', 'hl' => 'Kunjungan Wisatawan', 'val' => '1.240.500 Orang'],
            ['num' => 10, 'id' => 'Transportasi dan Komunikasi', 'en' => 'Transportation and Communication', 'hl' => 'Penumpang Kereta Api', 'val' => '3.450.200 Orang'],
            ['num' => 11, 'id' => 'Keuangan Daerah dan Harga', 'en' => 'Local Finance and Price', 'hl' => 'Realisasi Pendapatan Daerah', 'val' => 'Rp 4,28 Triliun'],
            ['num' => 12, 'id' => 'Pengeluaran Penduduk', 'en' => 'Household Consumption and Expenditure', 'hl' => 'Rata-rata Pengeluaran per Kapita', 'val' => 'Rp 1.150.000 / bln'],
            ['num' => 13, 'id' => 'Pendapatan Regional', 'en' => 'Regional Income', 'hl' => 'PDRB Atas Dasar Harga Berlaku', 'val' => 'Rp 92,45 Triliun'],
        ];
        foreach ($ddaChapters as $ch) {
            DB::table('chapter_narratives')->insert([
                'publication_id' => $ddaId,
                'chapter_number' => $ch['num'],
                'title_id' => $ch['id'],
                'title_en' => $ch['en'],
                'narrative_id' => 'Kabupaten Jember pada Bab '.$ch['num'].' ('.$ch['id'].') tahun '.$year.' mencatatkan kinerja pembangunan yang konsisten dan berkelanjutan di seluruh 31 wilayah kecamatan.',
                'narrative_en' => 'Jember Regency in Chapter '.$ch['num'].' ('.$ch['en'].') year '.$year.' recorded consistent and sustainable development performance across all 31 district areas.',
                'highlight_label' => $ch['hl'],
                'highlight_value' => $ch['val'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

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

        $skdChapters = [
            ['num' => 1, 'id' => 'Pendahuluan', 'en' => 'Introduction', 'hl' => 'Fokus Evaluasi', 'val' => 'Pelayanan Statistik Terpadu', 'desc_id' => 'Survei Kebutuhan Data (SKD) merupakan survei rutin BPS untuk mengevaluasi kualitas layanan data publikasi dan konsultasi statistik di BPS Kabupaten Jember tahun '.$year.'.', 'desc_en' => 'The Data Needs Survey (SKD) is a regular BPS survey to evaluate the service quality of publications and statistical consultations at BPS Jember Regency in '.$year.'.'],
            ['num' => 2, 'id' => 'Metodologi Survei Kebutuhan Data', 'en' => 'Survey Methodology', 'hl' => 'Metode Pencacahan', 'val' => 'Kuesioner VKD & Online', 'desc_id' => 'Pencacahan SKD tahun '.$year.' menggunakan metode kuesioner VKD digital dan tatap muka pada loket Pelayanan Statistik Terpadu (PST) BPS Kabupaten Jember.', 'desc_en' => 'The '.$year.' SKD data collection utilized digital VKD questionnaires and in-person interviews at the Integrated Statistical Service (PST) desk.'],
            ['num' => 3, 'id' => 'Karakteristik Konsumen PST', 'en' => 'Consumer Characteristics', 'hl' => 'Profil Mayoritas Konsumen', 'val' => 'Akademisi & Peneliti', 'desc_id' => 'Profil konsumen data BPS Kabupaten Jember tahun '.$year.' didominasi oleh segmen akademisi (mahasiswa/dosen) disusul instansi pemerintah daerah dan swasta.', 'desc_en' => 'Consumer profiles of BPS Jember Regency in '.$year.' were dominated by academic researchers followed by local government agencies and private sectors.'],
            ['num' => 4, 'id' => 'Analisis Kepuasan Layanan & Anti Korupsi', 'en' => 'Service Satisfaction & Anti-Corruption Analysis', 'hl' => 'Indeks Kepuasan & Anti Korupsi', 'val' => 'Mutu Sangat Baik (A)', 'desc_id' => 'Berdasarkan hasil survei tahun '.$year.', Indeks Kepuasan Konsumen (IKK) dan Indeks Persepsi Anti Korupsi (IPAK) BPS Kabupaten Jember masuk dalam kategori Sangat Baik dan Bebas Pungli.', 'desc_en' => 'Based on the '.$year.' survey, the Consumer Satisfaction Index (IKK) and Anti-Corruption Perception Index (IPAK) achieved Excellent category ratings.'],
            ['num' => 5, 'id' => 'Analisis Tingkat Kepentingan dan Kinerja (IPA)', 'en' => 'Importance-Performance Analysis (IPA)', 'hl' => 'Fokus Pembenahan', 'val' => 'Kuadran Prioritas Terjaga', 'desc_id' => 'Diagram Kartesius memetakan seluruh indikator pelayanan ke dalam Kuadran A, B, C, dan D untuk memprioritaskan pembenahan fasilitas dan akses data.', 'desc_en' => 'The Cartesian diagram maps all service indicators into Quadrants A, B, C, and D to prioritize improvements in statistical access.'],
        ];
        foreach ($skdChapters as $ch) {
            DB::table('chapter_narratives')->insert([
                'publication_id' => $skdId,
                'chapter_number' => $ch['num'],
                'title_id' => $ch['id'],
                'title_en' => $ch['en'],
                'narrative_id' => $ch['desc_id'],
                'narrative_en' => $ch['desc_en'],
                'highlight_label' => $ch['hl'],
                'highlight_value' => $ch['val'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

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

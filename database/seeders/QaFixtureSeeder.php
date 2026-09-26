<?php

namespace Database\Seeders;

use App\Models\Publication;
use Illuminate\Database\Seeder;

/**
 * Seeder khusus QA / pengujian otomatis (E2E & Feature Test).
 *
 * Menyiapkan SATU publikasi berstatus PENDING_DATA yang tidak pernah dikunci,
 * sehingga skrip pengujian dapat menguji alur unggah ingest tanpa menyentuh
 * publikasi produksi yang sudah APPROVED_LOCKED / FINAL_RELEASED.
 */
class QaFixtureSeeder extends Seeder
{
    public function run(): void
    {
        $pub = Publication::updateOrCreate(
            ['title' => 'Publikasi Uji Ingesti QA (Bukan Terbitan Resmi)'],
            [
                'type' => 'KDA',
                'district_id' => 1,
                'year' => (int) date('Y'),
                'catalog_number' => 'QA-0000.3509000',
                'publication_number' => 'QA-3509.0000',
                'issn' => '0000-0000',
                'volume' => '0',
                'book_size' => 'A5',
                'status' => 'PENDING_DATA',
            ]
        );

        // Tujuh bab standar KDA agar alur Redaksi -> Approval dapat diuji.
        $chapters = [
            ['Pendahuluan', 'Introduction'],
            ['Luas Wilayah dan Batas Administrasi', 'Area and Administrative Boundaries'],
            ['Pemerintahan', 'Government'],
            ['Demografi', 'Demography'],
            ['Pendidikan', 'Education'],
            ['Kesehatan', 'Health'],
            ['Pekerjaan', 'Employment'],
        ];

        foreach ($chapters as $i => [$id, $en]) {
            $pub->narratives()->updateOrCreate(
                ['chapter_number' => $i + 1],
                [
                    'title_id' => $id,
                    'title_en' => $en,
                    'narrative_id' => "Bab ini memuat data {$id} Kecamatan {{ nama_kecamatan }} tahun {{ tahun }} dengan luas wilayah {{ luas_wilayah }}. (Teks uji QA)",
                    'narrative_en' => "This chapter presents {$en} data for {{ nama_kecamatan }} in {{ tahun }} covering an area of {{ luas_wilayah }}. (QA draft text)",
                    'highlight_label' => 'Luas Wilayah',
                    'highlight_value' => '3.306,68 km²',
                ]
            );
        }
    }
}

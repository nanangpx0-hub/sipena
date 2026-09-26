<?php

namespace App\Console\Commands;

use App\Models\District;
use App\Models\Publication;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Inisialisasi seluruh master publikasi untuk tahun terbit baru.
 *
 * Contoh: php artisan sipena:init-year 2027
 *         php artisan sipena:init-year 2027 --from-year=2026
 *
 * Struktur 7 bab standar KDA disalin dari tahun acuan (bukan angka hasil
 * karangan), sehingga tidak ada angka resmi yang diarang-arang.
 */
class InitPublicationYearCommand extends Command
{
    protected $signature = 'sipena:init-year {year} : Tahun terbit target (mis. 2027)
                            {--from-year= : Tahun acuan sumber data (default: tahun target - 1)}';

    protected $description = 'Menduplikasi master DDA, SKD, dan 31 KDA (termasuk 7 bab) ke tahun terbit baru';

    /** Struktur 7 bab standar KDA (judul saja — angka tetap berasal dari data). */
    private const STANDARD_CHAPTERS = [
        [1, 'Geografi dan Iklim', 'Geography and Climate'],
        [2, 'Pemerintahan', 'Government'],
        [3, 'Penduduk', 'Population'],
        [4, 'Sosial dan Kesejahteraan Rakyat', 'Social and Welfare'],
        [5, 'Pertanian', 'Agriculture'],
        [6, 'Pariwisata, Transportasi, dan Komunikasi', 'Tourism and Transportation'],
        [7, 'Perbankan, Koperasi, dan Perdagangan', 'Banking, Cooperatives, and Trade'],
    ];

    public function handle(): int
    {
        $year = (int) $this->argument('year');
        $fromYear = (int) ($this->option('from-year') ?: $year - 1);

        if ($year < 1900 || $year > 2100) {
            $this->error("Tahun target tidak wajar: {$year}.");

            return self::FAILURE;
        }

        if ($year === $fromYear) {
            $this->error('Tahun acuan (--from-year) tidak boleh sama dengan tahun target.');

            return self::FAILURE;
        }

        if (! Schema::hasTable('publications') || ! Schema::hasTable('districts')) {
            $this->error('Tabel publications/districts belum tersedia. Jalankan migrate & seed terlebih dahulu.');

            return self::FAILURE;
        }

        $existing = Publication::where('year', $year)->count();
        if ($existing > 0) {
            $this->warn("Tahun {$year} sudah berisi {$existing} publikasi. Tidak ada yang diduplikasi (anti-duplikasi).");

            return self::SUCCESS;
        }

        $sourceYearExists = Publication::where('year', $fromYear)->exists();
        if (! $sourceYearExists) {
            $this->error("Tahun acuan {$fromYear} tidak ditemukan di database. Inisialisasi dibatalkan (tanpa angka karangan).");

            return self::FAILURE;
        }

        $this->info("Inisialisasi tahun terbit {$year} dari tahun acuan {$fromYear}...");

        try {
            DB::transaction(function () use ($year, $fromYear) {
                $createdDda = $this->duplicateDda($year, $fromYear);
                $createdSkd = $this->duplicateSkd($year, $fromYear);
                [$createdKda, $createdNarratives] = $this->duplicateKda($year, $fromYear);

                $this->newLine();
                $this->table(
                    ['Komponen', 'Jumlah'],
                    [
                        ['Publikasi DDA', $createdDda],
                        ['Publikasi SKD', $createdSkd],
                        ['Publikasi KDA (kecamatan)', $createdKda],
                        ['Bab ulasan chapter_narratives', $createdNarratives],
                        ['TOTAL publikasi tahun '.$year, $createdDda + $createdSkd + $createdKda],
                    ]
                );
            });
        } catch (\Throwable $e) {
            $this->error('Inisialisasi gagal dan di-rollback: '.$e->getMessage());

            return self::FAILURE;
        }

        $this->info("Selesai. Tahun {$year} siap menerima data (status PENDING_DATA).");

        return self::SUCCESS;
    }

    /**
     * Duplikasi 1 DDA: nomor publikasi 35090.{yy}01, volume naik 1, tenggat Feb.
     */
    protected function duplicateDda(int $year, int $fromYear): int
    {
        $source = Publication::where('type', 'DDA')->where('year', $fromYear)->first();
        if (! $source) {
            $this->warn("DDA {$fromYear} tidak ditemukan — DDA dilewati.");

            return 0;
        }

        Publication::create([
            'type' => 'DDA',
            'district_id' => null,
            'year' => $year,
            'title' => "Kabupaten Jember Dalam Angka {$year}",
            'catalog_number' => '1102001.3509',
            'publication_number' => '35090.'.substr((string) $year, 2).'01',
            'issn' => $source->issn,
            'volume' => $this->incrementVolume($source->volume),
            'book_size' => 'A5',
            'status' => 'PENDING_DATA',
            'soft_deadline' => sprintf('%04d-02-15 00:00:00', $year),
            'hard_deadline' => sprintf('%04d-02-28 00:00:00', $year),
        ]);

        $this->line("  + DDA: Kabupaten Jember Dalam Angka {$year}");

        return 1;
    }

    /**
     * Duplikasi 1 SKD: nomor publikasi 35090.{yy}02, volume naik 1, tenggat Nov.
     */
    protected function duplicateSkd(int $year, int $fromYear): int
    {
        $source = Publication::where('type', 'SKD')->where('year', $fromYear)->first();
        if (! $source) {
            $this->warn("SKD {$fromYear} tidak ditemukan — SKD dilewati.");

            return 0;
        }

        Publication::create([
            'type' => 'SKD',
            'district_id' => null,
            'year' => $year,
            'title' => "Analisis Hasil Survei Kebutuhan Data BPS Kabupaten Jember {$year}",
            'catalog_number' => '1305011.3509',
            'publication_number' => '35090.'.substr((string) $year, 2).'02',
            'issn' => $source->issn,
            'volume' => $this->incrementVolume($source->volume),
            'book_size' => '18,2 x 25,7',
            'status' => 'PENDING_DATA',
            'soft_deadline' => sprintf('%04d-11-15 00:00:00', $year),
            'hard_deadline' => sprintf('%04d-11-30 00:00:00', $year),
        ]);

        $this->line("  + SKD: Analisis Hasil Survei Kebutuhan Data {$year}");

        return 1;
    }

    /**
     * Duplikasi 31 KDA + salinan 7 bab ulasan per kecamatan.
     *
     * @return array{0: int, 1: int} [jumlah KDA, jumlah bab]
     */
    protected function duplicateKda(int $year, int $fromYear): array
    {
        $districts = District::orderBy('bps_code')->get();
        if ($districts->isEmpty()) {
            $this->warn('Tabel districts kosong — KDA dilewati.');

            return [0, 0];
        }

        $createdKda = 0;
        $createdNarratives = 0;

        foreach ($districts as $district) {
            $source = Publication::where('type', 'KDA')
                ->where('district_id', $district->id)
                ->where('year', $fromYear)
                ->first();

            if (! $source) {
                $this->warn("  ! KDA {$fromYear} untuk Kecamatan {$district->name} tidak ada — baris dilewati.");
            }

            $publication = Publication::create([
                'type' => 'KDA',
                'district_id' => $district->id,
                'year' => $year,
                'title' => "Kecamatan {$district->name} Dalam Angka {$year}",
                'catalog_number' => '1102001.3509'.substr($district->bps_code, 4),
                'publication_number' => '35090.'.substr((string) $year, 2).substr($district->bps_code, 4),
                'issn' => $source?->issn,
                'volume' => $source ? $this->incrementVolume($source->volume) : null,
                'book_size' => 'A5',
                'status' => 'PENDING_DATA',
                'soft_deadline' => sprintf('%04d-09-15 00:00:00', $year),
                'hard_deadline' => sprintf('%04d-09-26 00:00:00', $year),
            ]);
            $createdKda++;

            $createdNarratives += $this->copyChapters($publication, $source, $fromYear, $year);
        }

        $this->line("  + KDA: {$createdKda} kecamatan, {$createdNarratives} bab ulasan");

        return [$createdKda, $createdNarratives];
    }

    /**
     * Salin 7 bab dari tahun acuan (token tahun diperbarui); bila tahun acuan
     * belum punya bab, buat struktur judul bab standar tanpa angka apa pun.
     */
    protected function copyChapters(Publication $publication, ?Publication $source, int $fromYear, int $year): int
    {
        if ($source) {
            $sourceNarratives = DB::table('chapter_narratives')
                ->where('publication_id', $source->id)
                ->orderBy('chapter_number')
                ->get();

            if ($sourceNarratives->isNotEmpty()) {
                foreach ($sourceNarratives as $row) {
                    DB::table('chapter_narratives')->insert([
                        'publication_id' => $publication->id,
                        'chapter_number' => $row->chapter_number,
                        'title_id' => $row->title_id,
                        'title_en' => $row->title_en,
                        'narrative_id' => $this->replaceYear($row->narrative_id, $fromYear, $year),
                        'narrative_en' => $this->replaceYear($row->narrative_en, $fromYear, $year),
                        'highlight_label' => $row->highlight_label,
                        'highlight_value' => $row->highlight_value,
                        'last_edited_by' => null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }

                return $sourceNarratives->count();
            }
        }

        // Fallback: struktur judul saja (tanpa angka hasil karangan).
        foreach (self::STANDARD_CHAPTERS as [$number, $titleId, $titleEn]) {
            DB::table('chapter_narratives')->insert([
                'publication_id' => $publication->id,
                'chapter_number' => $number,
                'title_id' => $titleId,
                'title_en' => $titleEn,
                'narrative_id' => null,
                'narrative_en' => null,
                'highlight_label' => null,
                'highlight_value' => null,
                'last_edited_by' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return count(self::STANDARD_CHAPTERS);
    }

    /**
     * Ganti tahun acuan menjadi tahun target sebagai token bilangan utuh saja
     * (agar "2026" di dalam "120260" tidak ikut berubah).
     */
    protected function replaceYear(?string $text, int $fromYear, int $year): ?string
    {
        if ($text === null || $text === '') {
            return $text;
        }

        return (string) preg_replace('/\b'.$fromYear.'\b/', (string) $year, $text);
    }

    protected function incrementVolume(?string $volume): ?string
    {
        if ($volume === null || $volume === '' || ! is_numeric($volume)) {
            return $volume;
        }

        return (string) ((int) $volume + 1);
    }
}

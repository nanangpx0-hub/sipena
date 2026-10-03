<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

/*
|--------------------------------------------------------------------------
| Perintah QA SI-PENA
|--------------------------------------------------------------------------
| `php artisan sipena:qa-reset`
| Mengembalikan publikasi fixture QA ke kondisi awal (PENDING_DATA) serta
| membersihkan tabel ingest & log workflow fixture tersebut, agar skrip
| pengujian otomatis (E2E / UAT) dapat diulang tanpa reset database manual.
*/
Artisan::command('sipena:qa-reset', function () {
    $title = 'Publikasi Uji Ingesti QA (Bukan Terbitan Resmi)';

    if (! Schema::hasTable('publications')) {
        $this->error('Tabel publications belum tersedia. Jalankan migrate & seed terlebih dahulu.');

        return 1;
    }

    $pub = DB::table('publications')->where('title', $title)->first();
    if (! $pub) {
        $this->warn("Fixture QA '{$title}' tidak ditemukan. Jalankan: php artisan db:seed --class=QaFixtureSeeder");

        return 1;
    }

    DB::table('publication_tables')->where('publication_id', $pub->id)->delete();
    DB::table('workflow_logs')->where('publication_id', $pub->id)->delete();
    DB::table('raw_data_files')->where('publication_id', $pub->id)->delete();
    DB::table('publications')->where('id', $pub->id)->update(['status' => 'PENDING_DATA']);

    $this->info("Fixture QA (publikasi #{$pub->id}) dikembalikan ke PENDING_DATA dan data uji dibersihkan.");

    return 0;
})->purpose('Reset fixture QA SI-PENA agar skrip pengujian dapat diulang');

/*
|--------------------------------------------------------------------------
| Pembersihan Berkas Sementara SI-PENA
|--------------------------------------------------------------------------
| `php artisan sipena:cleanup-temp {--days=7}`
| Membersihkan berkas .typ dan artefak sementara yang berumur lebih dari N hari
| agar direktori storage/temp tidak membebani kapasitas disk server.
*/
Artisan::command('sipena:cleanup-temp {--days=7 : Hapus berkas lebih tua dari N hari}', function () {
    $days = (int) $this->option('days');
    $tempDir = storage_path('temp');

    if (! is_dir($tempDir)) {
        $this->info('Direktori storage/temp tidak ditemukan.');

        return 0;
    }

    $cutoff = time() - ($days * 86400);
    $deleted = 0;

    foreach (scandir($tempDir) as $item) {
        if (in_array($item, ['.', '..', '.gitignore'], true)) {
            continue;
        }

        $path = $tempDir.DIRECTORY_SEPARATOR.$item;
        if (is_file($path) && filemtime($path) < $cutoff) {
            @unlink($path);
            $deleted++;
        }
    }

    $this->info("Pembersihan selesai: {$deleted} berkas sementara (lebih tua dari {$days} hari) berhasil dihapus.");

    return 0;
})->purpose('Bersihkan berkas Typst dan artefak sementara di storage/temp')->daily();

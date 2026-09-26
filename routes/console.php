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

    if (!Schema::hasTable('publications')) {
        $this->error('Tabel publications belum tersedia. Jalankan migrate & seed terlebih dahulu.');

        return 1;
    }

    $pub = DB::table('publications')->where('title', $title)->first();
    if (!$pub) {
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

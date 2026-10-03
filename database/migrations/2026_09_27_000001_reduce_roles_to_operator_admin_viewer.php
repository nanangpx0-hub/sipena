<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Penyederhanaan RBAC SI-PENA menjadi tiga peran: operator, admin, viewer.
 *
 * Peran `editor` dan `approver` dilebur menjadi `admin` (hak penuh), seluruh
 * pengguna pada peran lama dialihkan ke `admin` lebih dulu sehingga tidak ada
 * pengguna yang terhapus oleh onDelete cascade foreign key `users.role_id`.
 */
return new class extends Migration
{
    public function up(): void
    {
        $adminId = DB::table('roles')->where('name', 'admin')->value('id');

        if ($adminId === null) {
            $adminId = DB::table('roles')->insertGetId([
                'name' => 'admin',
                'display_name' => 'Administrator / Ketua Tim & Editor Bahasa',
                'description' => 'Hak penuh: menyunting ulasan bab, mengunci & menyetujui publikasi, mengunggah aset, serta kompilasi PDF final.',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $legacyIds = DB::table('roles')
            ->whereIn('name', ['editor', 'approver'])
            ->pluck('id');

        if ($legacyIds->isNotEmpty()) {
            DB::table('users')
                ->whereIn('role_id', $legacyIds)
                ->update(['role_id' => $adminId]);

            DB::table('roles')->whereIn('id', $legacyIds)->delete();
        }
    }

    public function down(): void
    {
        $now = now();

        $restore = [
            ['editor', 'Editor Bahasa & Ulasan (Editorial Specialist)', 'Menyunting teks ulasan bab bilingual (ID/EN), menyesuaikan redaksi, dan upload aset visual.'],
            ['approver', 'Ketua Tim / Approver (Koordinator Tim)', 'Memvalidasi konsistensi tabel, mengunci status bab, menyetujui draf, dan kompilasi PDF final.'],
        ];

        foreach ($restore as [$name, $display, $description]) {
            $exists = DB::table('roles')->where('name', $name)->exists();
            if (! $exists) {
                DB::table('roles')->insert([
                    'name' => $name,
                    'display_name' => $display,
                    'description' => $description,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }

        // Pengguna tidak dipisahkan kembali (tidak dapat dipastikan peran asalnya).
    }
};

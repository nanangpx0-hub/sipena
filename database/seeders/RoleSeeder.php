<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            [
                'name' => 'operator',
                'display_name' => 'Operator Data (Data Specialist)',
                'description' => 'Mengunggah file Excel mentah OPD, mencocokkan mapping kolom, dan memverifikasi data tabel.',
            ],
            [
                'name' => 'admin',
                'display_name' => 'Administrator / Ketua Tim & Editor Bahasa',
                'description' => 'Hak penuh: menyunting ulasan bab, mengunci & menyetujui publikasi, mengunggah aset, serta kompilasi PDF final.',
            ],
            [
                'name' => 'viewer',
                'display_name' => 'Pimpinan / Viewer (Pimpinan BPS)',
                'description' => 'Akses baca (read-only), pemantauan progres rilis 31 kecamatan, dan unduh draf.',
            ],
        ];

        foreach ($roles as $r) {
            DB::table('roles')->updateOrInsert(
                ['name' => $r['name']],
                [
                    'display_name' => $r['display_name'],
                    'description' => $r['description'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}

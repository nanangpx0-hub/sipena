<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $adminRole = DB::table('roles')->where('name', 'admin')->first();
        $operatorRole = DB::table('roles')->where('name', 'operator')->first();
        $viewerRole = DB::table('roles')->where('name', 'viewer')->first();

        $users = [
            [
                'nip' => '198501012010011001',
                'name' => 'Koordinator Publikasi (Approver)',
                'email' => 'approver@bps3509.go.id',
                'password' => Hash::make('password123'),
                'role_id' => $adminRole->id,
                'is_active' => true,
            ],
            [
                'nip' => '199203152015021002',
                'name' => 'Operator Data OPD',
                'email' => 'operator@bps3509.go.id',
                'password' => Hash::make('password123'),
                'role_id' => $operatorRole->id,
                'is_active' => true,
            ],
            [
                'nip' => '199407202018032003',
                'name' => 'Editor Bahasa & Ulasan',
                'email' => 'editor@bps3509.go.id',
                'password' => Hash::make('password123'),
                'role_id' => $adminRole->id,
                'is_active' => true,
            ],
            [
                'nip' => '198005122005011004',
                'name' => 'Kepala BPS Jember (Viewer)',
                'email' => 'viewer@bps3509.go.id',
                'password' => Hash::make('password123'),
                'role_id' => $viewerRole->id,
                'is_active' => true,
            ],
        ];

        foreach ($users as $u) {
            DB::table('users')->updateOrInsert(
                ['nip' => $u['nip']],
                [
                    'name' => $u['name'],
                    'email' => $u['email'],
                    'password' => $u['password'],
                    'role_id' => $u['role_id'],
                    'is_active' => $u['is_active'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}

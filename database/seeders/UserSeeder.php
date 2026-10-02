<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Location;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $defaultPassword = 'password';

        // ==== Ambil Department ====
        $sisDept = Department::where('code', 'SIS')->first();
        $keuDept = Department::where('code', 'keu')->first();
        $humasDept = Department::where('code', 'HUMAS')->first();
        $engDept = Department::where('code', 'ENGINEERING')->first();

        // ==== Ambil Location ====
        $sisRoom = Location::where('room', 'SIS')->first();
        $keuRoom = Location::where('room', 'KEUANGAN')->first();
        $humasRoom = Location::where('room', 'HUMAS')->first();
        $engRoom = Location::where('room', 'ENGEENERING')->first();

        // ==== Daftar semua user (default + pegawai) ====
        $users = [
            // --- Default Users ---
            [
                'nip' => '10000001',
                'name' => 'Admin',
                'email' => 'admin@admin.com',
                'phone' => '081200000001',
                'position' => 'System Administrator',
                'role' => 'admin',
                'dept' => $sisDept,
                'loc' => $sisRoom,
            ],
            [
                'nip' => '1000TR',
                'username' => 'ruslan',
                'name' => 'RUSLAN',
                'email' => 'ruslan@gmail.com',
                'phone' => '',
                'position' => 'Teknisi',
                'role' => 'support',
                'dept' => $sisDept,
                'loc' => $sisRoom,
            ],
            [
                'nip' => '1001TR',
                'username' => 'rizki.darmawan',
                'name' => 'RIZKI DARMAWAN',
                'email' => 'rizki.darmawan@gmail.com',
                'phone' => '',
                'position' => 'Teknisi',
                'role' => 'support',
                'dept' => $sisDept,
                'loc' => $sisRoom,
            ],
            [
                'nip' => '801503524HK',
                'username' => 'sapta.dewangga',
                'name' => 'SAPTA DEWANGGA',
                'email' => 'sapta.dewangga@gmail.com',
                'phone' => '',
                'position' => 'Teknisi',
                'role' => 'support',
                'dept' => $sisDept,
                'loc' => $sisRoom,
            ],
            [
                'nip' => '1002TR',
                'username' => 'darmawan',
                'name' => 'DARMAWAN',
                'email' => 'darmawan@gmail.com',
                'phone' => '',
                'position' => 'Teknisi',
                'role' => 'support',
                'dept' => $sisDept,
                'loc' => $sisRoom,
            ],
            [
                'nip' => '10000003',
                'name' => 'User',
                'email' => 'user@admin.com',
                'phone' => '081200000003',
                'position' => 'Staff',
                'role' => 'user',
                'dept' => null,
                'loc' => null,
            ],

            // --- Pegawai ---
            [
                'nip' => '20000001',
                'name' => 'Budi Santoso',
                'email' => 'budi.santoso@perusahaan.com',
                'phone' => '081200000011',
                'position' => 'IT Support',
                'role' => 'user',
                'dept' => $sisDept,
                'loc' => $sisRoom,
            ],
            [
                'nip' => '20000002',
                'name' => 'Siti Aminah',
                'email' => 'siti.aminah@perusahaan.com',
                'phone' => '081200000012',
                'position' => 'Staff Keuangan',
                'role' => 'user',
                'dept' => $keuDept,
                'loc' => $keuRoom,
            ],
            [
                'nip' => '20000003',
                'name' => 'Andi Wijaya',
                'email' => 'andi.wijaya@perusahaan.com',
                'phone' => '081200000013',
                'position' => 'Staff Humas',
                'role' => 'user',
                'dept' => $humasDept,
                'loc' => $humasRoom,
            ],
            [
                'nip' => '20000004',
                'name' => 'Dewi Lestari',
                'email' => 'dewi.lestari@perusahaan.com',
                'phone' => '081200000014',
                'position' => 'Manager Keuangan',
                'role' => 'user',
                'dept' => $keuDept,
                'loc' => $keuRoom,
            ],
            [
                'nip' => '20000005',
                'name' => 'Rudi Hartono',
                'email' => 'rudi.hartono@perusahaan.com',
                'phone' => '081200000015',
                'position' => 'Engineer',
                'role' => 'user',
                'dept' => $engDept,
                'loc' => $engRoom,
            ],
        ];

        foreach ($users as $data) {
            $username = User::generateUsername($data['email']);
            $existing = User::withTrashed()->where('email', $data['email'])->first();
            if ($existing) {
                $username = $existing->username;
            }

            User::updateOrCreate(
                ['email' => $data['email']],
                [
                    'nip' => $data['nip'],
                    'username' => $username,
                    'name' => $data['name'],
                    'password' => Hash::make($defaultPassword),
                    'phone' => $data['phone'],
                    'position' => $data['position'],
                    'role' => $data['role'],
                    'is_active' => true,
                    'email_verified_at' => now(),
                    'deleted_at' => null, // restore kalau pernah di-soft delete
                    'department_id' => $data['dept']?->id,
                    'location_id' => $data['loc']?->id,
                ]
            );

            $this->command->info(" {$data['role']} created: {$username} / {$defaultPassword}");
        }

        $this->command->newLine();
        $this->command->info('🎉 All users seeded successfully! Total: ' . count($users));
    }
}

<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $defaultPassword = 'password123';

        // ============================================================
        // DAFTAR USER DEFAULT
        // ============================================================
        $users = [
            [
                'nip' => '10000001',
                'name' => 'Admin',
                'email' => 'admin@admin.com',
                'phone' => '081200000001',
                'position' => 'System Administrator',
                'role' => 'admin',
            ],
            [
                'nip' => '10000002',
                'name' => 'Support',
                'email' => 'support@admin.com',
                'phone' => '081200000002',
                'position' => 'IT Support',
                'role' => 'support',
            ],
            [
                'nip' => '10000003',
                'name' => 'User',
                'email' => 'user@admin.com',
                'phone' => '081200000003',
                'position' => 'Staff',
                'role' => 'user',
            ],
        ];

        // ============================================================
        // CREATE / UPDATE USER
        // ============================================================
        foreach ($users as $data) {
            // Generate username dari email sebelum @
            // Contoh: admin@admin.com → admin
            $username = User::generateUsername($data['email']);

            // Cek apakah user sudah ada (by email) — kalau ada, pakai username lama
            $existing = User::withTrashed()->where('email', $data['email'])->first();
            if ($existing) {
                $username = $existing->username;
            }

            $user = User::updateOrCreate(
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
                ]
            );

            $this->command->info("✅ {$data['role']} created: {$username} / {$defaultPassword}");
        }

        $this->command->newLine();
        $this->command->info('🎉 All default users seeded successfully!');
    }
}

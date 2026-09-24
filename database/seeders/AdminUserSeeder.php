<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // ============================================================
        // 1. ADMIN UTAMA
        // ============================================================
        User::updateOrCreate(
            ['email' => 'admin@admin.com'],
            [
                'name' => 'Admin',
                'password' => Hash::make('password123'),
                'role' => 'admin',
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        $this->command->info('✅ Admin user created: admin@admin.com / password123');

        // ============================================================
        // 2. SUPPORT (Opsional)
        // ============================================================
        User::updateOrCreate(
            ['email' => 'support@admin.com'],
            [
                'name' => 'Support',
                'password' => Hash::make('password123'),
                'role' => 'support',
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        $this->command->info('✅ Support user created: support@admin.com / password123');

        // ============================================================
        // 3. USER BIASA (Opsional)
        // ============================================================
        User::updateOrCreate(
            ['email' => 'user@admin.com'],
            [
                'name' => 'User',
                'password' => Hash::make('password123'),
                'role' => 'user',
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        $this->command->info('✅ Regular user created: user@admin.com / password123');
    }
}

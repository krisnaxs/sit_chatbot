<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $this->call([
                // ==== 1. MASTER DATA (urutan wajib) ====
            DepartmentSeeder::class,        // Harus pertama
            LocationSeeder::class,          // Harus kedua
            UserSeeder::class,              // Butuh dept & location

                // ==== 2. MASTER DATA LAIN ====
            VendorSeeder::class,
            AssetCategorySeeder::class,     // Harus sebelum AssetSeeder
            AssetTypeSeeder::class,

                // ==== 3. TRANSAKSI ASET ====
            AssetSeeder::class,             // Butuh category & vendor
            AssetTransactionSeeder::class,  // Butuh asset & user
            ConsumableSeeder::class,        // Butuh category & user

                // ==== 4. APLIKASI & KNOWLEDGE ====
            AppSeeder::class,
            KnowledgeSeeder::class,

            // ==== 5. SIAM (jika ada) ====
            // SiamSeeder::class,           // ← pastikan file-nya ada
        ]);
    }
}

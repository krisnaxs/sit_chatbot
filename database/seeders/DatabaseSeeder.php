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
            DepartmentSeeder::class,        // Harus pertama
            LocationSeeder::class,          // Harus kedua
            UserSeeder::class,              // Butuh dept & location
            VendorSeeder::class,
            AssetCategorySeeder::class,     // Harus sebelum AssetSeeder
            AssetTypeSeeder::class,
            AssetSeeder::class,             // Butuh category & vendor
            AssetTransactionSeeder::class,  // Butuh asset & user
            ConsumableSeeder::class,        // Butuh category & user
            AppSeeder::class,
            KnowledgeSeeder::class,
        ]);
    }
}

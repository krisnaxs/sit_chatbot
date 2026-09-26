<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class SiamSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            MasterDataSeeder::class,
            AssetSeeder::class,
            ConsumableSeeder::class,
            AssetTransactionSeeder::class,
        ]);

        $this->command->newLine();
        $this->command->info('🎉 SIAM seeder selesai!');
    }
}

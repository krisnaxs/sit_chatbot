<?php

namespace Database\Seeders;

use App\Models\AssetType;
use Illuminate\Database\Seeder;

class AssetTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            // Lenovo
            ['brand' => 'Lenovo', 'model' => 'ThinkPad T14 Gen 4'],
            ['brand' => 'Lenovo', 'model' => 'ThinkPad X1 Carbon Gen 11'],
            ['brand' => 'Lenovo', 'model' => 'ThinkPad E14 Gen 5'],
            ['brand' => 'Lenovo', 'model' => 'IdeaPad Slim 3'],

            // HP
            ['brand' => 'HP', 'model' => 'ProDesk 400 G9'],
            ['brand' => 'HP', 'model' => 'EliteBook 840 G10'],
            ['brand' => 'HP', 'model' => 'LaserJet Pro M404'],

            // Epson
            ['brand' => 'Epson', 'model' => 'L3110 All-in-One'],
            ['brand' => 'Epson', 'model' => 'L3210 All-in-One'],

            // Dell
            ['brand' => 'Dell', 'model' => 'Latitude 5420'],
            ['brand' => 'Dell', 'model' => 'OptiPlex 3000'],

            // Asus
            ['brand' => 'Asus', 'model' => 'VivoBook 14'],
        ];

        foreach ($types as $t) {
            AssetType::updateOrCreate(
                ['brand' => $t['brand'], 'model' => $t['model']],
                $t
            );
        }

        $this->command->info('✅ Asset types seeded: ' . count($types));
    }
}

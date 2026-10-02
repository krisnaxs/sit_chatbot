<?php

namespace Database\Seeders;

use App\Models\AssetType;
use Illuminate\Database\Seeder;

class AssetTypeSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            ['brand' => 'LENOVO', 'model' => 'THINPAD E 14 GEN 4', 'description' => null],
            ['brand' => 'LENOVO', 'model' => 'THINPAD E 14 GEN 5', 'description' => null],
            ['brand' => 'LENOVO', 'model' => 'THINPAD E 14 GEN 6', 'description' => null],
            ['brand' => 'PRINTER', 'model' => 'EPSON L 5290', 'description' => null],
            ['brand' => 'LENOVO', 'model' => 'THINK CENTRE M 90 A', 'description' => null],
            ['brand' => 'LENOVO', 'model' => 'THINK CENTRE M 820 Z', 'description' => null],
            ['brand' => 'LENOVO', 'model' => 'THINKPAD T 14', 'description' => null],
        ];

        foreach ($data as $d) {
            AssetType::updateOrCreate(
                ['brand' => $d['brand'], 'model' => $d['model']],
                $d + ['is_active' => true]
            );
        }

        $this->command->info(' Asset Types: ' . count($data));
    }
}

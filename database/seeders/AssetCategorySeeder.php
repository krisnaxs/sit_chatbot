<?php

namespace Database\Seeders;

use App\Models\AssetCategory;
use Illuminate\Database\Seeder;

class AssetCategorySeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            ['name' => 'PRINTER', 'code' => 'PRT', 'is_consumable' => false],
            ['name' => 'LAPTOP', 'code' => 'LPT', 'is_consumable' => false],
            ['name' => 'KOMPUTER', 'code' => 'PC', 'is_consumable' => false],
            ['name' => 'SCANNER', 'code' => 'SCN', 'is_consumable' => false],
            ['name' => 'RAM', 'code' => 'RAM', 'is_consumable' => true],
            ['name' => 'HARDISK', 'code' => 'HDD', 'is_consumable' => true],
            ['name' => 'SSD', 'code' => 'SSD', 'is_consumable' => true],
            ['name' => 'KEYBOARD', 'code' => 'KYB', 'is_consumable' => true],
            ['name' => 'MOUSE', 'code' => 'MS', 'is_consumable' => true],
            ['name' => 'SPEAKER', 'code' => 'JBL', 'is_consumable' => true],
            ['name' => 'CHARGER', 'code' => 'CHR', 'is_consumable' => true],
            ['name' => 'HDMI', 'code' => 'HDM', 'is_consumable' => true],
            ['name' => 'KABEL LAN', 'code' => 'LAN', 'is_consumable' => true],
            ['name' => 'CONVERTER', 'code' => 'CNV', 'is_consumable' => true],
        ];

        foreach ($data as $c) {
            // Kategori yang butuh agent monitoring: Laptop, PC, Scanner
            $isAgentMonitored = in_array($c['code'], ['LPT', 'PC', 'SCN'], true);

            AssetCategory::updateOrCreate(
                ['code' => $c['code']],
                $c + [
                    'description' => null,
                    'is_active' => true,
                    'is_agent_monitored' => $isAgentMonitored,
                ]
            );
        }

        $this->command->info(' Categories: ' . count($data));
    }
}

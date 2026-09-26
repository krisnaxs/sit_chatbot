<?php

namespace Database\Seeders;

use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\AssetOwnership;
use App\Models\Vendor;
use Illuminate\Database\Seeder;

class AssetSeeder extends Seeder
{
    public function run(): void
    {
        $categories = AssetCategory::pluck('id', 'code')->toArray();

        $lenovoVendor = Vendor::where('name', 'PT Lenovo Indonesia')->first();
        $sewaVendor = Vendor::where('name', 'PT Sewa Komputer Indonesia')->first();
        $mitraVendor = Vendor::where('name', 'CV Mitra Office Supply')->first();

        $counter = 0;

        // ===== 40 Lenovo T14 =====
        for ($i = 1; $i <= 40; $i++) {
            $counter++;
            $isLeased = $i > 30;

            $asset = Asset::create([
                'asset_code' => 'AST-2026-' . str_pad($counter, 4, '0', STR_PAD_LEFT),
                'serial_number' => 'T14-SN-' . str_pad($i, 4, '0', STR_PAD_LEFT),
                'hostname' => 'NB-T14-' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'brand' => 'Lenovo',
                'model' => 'ThinkPad T14 Gen 4',
                'category_id' => $categories['LPT'] ?? null,
                'specification' => [
                    'cpu' => 'Intel Core i5-1335U',
                    'ram' => '16 GB DDR4',
                    'storage' => '512 GB NVMe SSD',
                    'gpu' => 'Intel Iris Xe',
                    'screen' => '14" FHD',
                ],
                'os' => 'Windows 11 Pro',
                'os_license' => 'OEM',
                'ownership_type' => $isLeased ? 'leased' : 'owned',
                'purchase_date' => now()->subMonths(rand(3, 24)),
                'purchase_price' => $isLeased ? 12000000 : 15500000,
                'warranty_expire' => now()->addYears(2),
                'status' => 'available',
                'condition_percent' => rand(70, 100),
            ]);

            AssetOwnership::create([
                'asset_id' => $asset->id,
                'vendor_id' => $isLeased ? $sewaVendor?->id : $lenovoVendor?->id,
                'ownership_type' => $asset->ownership_type,
                'purchase_price' => $isLeased ? null : $asset->purchase_price,
                'invoice_number' => $isLeased ? null : 'INV-2026-' . str_pad($i, 4, '0', STR_PAD_LEFT),
                'contract_number' => $isLeased ? 'SEWA-2026-' . str_pad($i, 4, '0', STR_PAD_LEFT) : null,
                'contract_start' => $isLeased ? now()->subMonths(6) : null,
                'contract_end' => $isLeased ? now()->addMonths(18) : null,
                'monthly_cost' => $isLeased ? 500000 : null,
                'pic_vendor' => $isLeased ? 'Bpk. Joko' : 'Ibu Rina',
            ]);
        }

        // ===== 10 PC Desktop =====
        for ($i = 1; $i <= 10; $i++) {
            $counter++;
            $asset = Asset::create([
                'asset_code' => 'AST-2026-' . str_pad($counter, 4, '0', STR_PAD_LEFT),
                'serial_number' => 'PC-SN-' . str_pad($i, 4, '0', STR_PAD_LEFT),
                'hostname' => 'PC-DESK-' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'brand' => 'HP',
                'model' => 'ProDesk 400 G9',
                'category_id' => $categories['PC'] ?? null,
                'specification' => [
                    'cpu' => 'Intel Core i5-12500',
                    'ram' => '8 GB DDR4',
                    'storage' => '1 TB HDD + 256 GB SSD',
                ],
                'os' => 'Windows 11 Pro',
                'os_license' => 'OEM',
                'ownership_type' => 'owned',
                'purchase_date' => now()->subMonths(rand(6, 18)),
                'purchase_price' => 8500000,
                'warranty_expire' => now()->addYear(),
                'status' => 'available',
                'condition_percent' => rand(80, 100),
            ]);

            AssetOwnership::create([
                'asset_id' => $asset->id,
                'vendor_id' => $mitraVendor?->id,
                'ownership_type' => 'owned',
                'purchase_price' => 8500000,
                'invoice_number' => 'INV-PC-2026-' . str_pad($i, 4, '0', STR_PAD_LEFT),
                'pic_vendor' => 'Bpk. Anton',
            ]);
        }

        // ===== 5 Printer =====
        for ($i = 1; $i <= 5; $i++) {
            $counter++;
            $isLeased = $i > 3;

            $asset = Asset::create([
                'asset_code' => 'AST-2026-' . str_pad($counter, 4, '0', STR_PAD_LEFT),
                'serial_number' => 'PRN-SN-' . str_pad($i, 4, '0', STR_PAD_LEFT),
                'hostname' => null,
                'brand' => 'Epson',
                'model' => 'L3110 All-in-One',
                'category_id' => $categories['PRN'] ?? null,
                'specification' => [
                    'type' => 'Inkjet',
                    'fungsi' => 'Print, Scan, Copy',
                ],
                'os' => null,
                'ownership_type' => $isLeased ? 'leased' : 'owned',
                'purchase_date' => now()->subMonths(rand(3, 12)),
                'purchase_price' => 3500000,
                'warranty_expire' => now()->addYear(),
                'status' => 'available',
                'condition_percent' => rand(75, 100),
            ]);

            AssetOwnership::create([
                'asset_id' => $asset->id,
                'vendor_id' => $isLeased ? $sewaVendor?->id : $mitraVendor?->id,
                'ownership_type' => $asset->ownership_type,
                'purchase_price' => $isLeased ? null : 3500000,
                'monthly_cost' => $isLeased ? 200000 : null,
                'contract_start' => $isLeased ? now()->subMonths(3) : null,
                'contract_end' => $isLeased ? now()->addMonths(9) : null,
                'pic_vendor' => $isLeased ? 'Bpk. Joko' : 'Bpk. Anton',
            ]);
        }

        $this->command->info("✅ Assets: {$counter} unit (40 T14, 10 PC, 5 Printer)");
    }
}

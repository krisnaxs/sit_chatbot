<?php

namespace Database\Seeders;

use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\Consumable;
use App\Models\ConsumableTransaction;
use App\Models\User;
use Illuminate\Database\Seeder;

class ConsumableSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedConsumables();
        $this->seedConsumableTransactions();
    }

    // ================================================================
    private function seedConsumables(): void
    {
        $categories = AssetCategory::pluck('id', 'code')->toArray();

        $data = [
            [
                'name' => 'Mouse Logitech M170',
                'category_id' => $categories['MSE'] ?? null,
                'brand' => 'Logitech',
                'model' => 'M170',
                'unit' => 'pcs',
                'stock_total' => 50,
                'stock_available' => 50,
                'stock_minimum' => 10,
                'last_price' => 120000,
            ],
            [
                'name' => 'Keyboard Logitech K120',
                'category_id' => $categories['KBD'] ?? null,
                'brand' => 'Logitech',
                'model' => 'K120',
                'unit' => 'pcs',
                'stock_total' => 30,
                'stock_available' => 30,
                'stock_minimum' => 5,
                'last_price' => 150000,
            ],
            [
                'name' => 'HDD External Seagate 1TB',
                'category_id' => $categories['HDD'] ?? null,
                'brand' => 'Seagate',
                'model' => 'Expansion 1TB',
                'unit' => 'pcs',
                'stock_total' => 10,
                'stock_available' => 10,
                'stock_minimum' => 2,
                'last_price' => 850000,
            ],
        ];

        foreach ($data as $d) {
            Consumable::updateOrCreate(['name' => $d['name']], $d);
        }

        $this->command->info('✅ Consumables: ' . count($data));
    }

    // ================================================================
    private function seedConsumableTransactions(): void
    {
        $admin = User::where('email', 'admin@admin.com')->first();
        $budi = User::where('email', 'budi.santoso@perusahaan.com')->first();
        $siti = User::where('email', 'siti.aminah@perusahaan.com')->first();
        $andi = User::where('email', 'andi.wijaya@perusahaan.com')->first();
        $rudi = User::where('email', 'rudi.hartono@perusahaan.com')->first();

        $mouse = Consumable::where('name', 'Mouse Logitech M170')->first();
        $keyboard = Consumable::where('name', 'Keyboard Logitech K120')->first();
        $hdd = Consumable::where('name', 'HDD External Seagate 1TB')->first();

        if (!$mouse || !$keyboard || !$hdd) {
            $this->command->warn('⚠️  Consumable tidak lengkap — transactions dilewati.');
            return;
        }

        $laptops = Asset::where('hostname', 'like', 'NB-T14-%')
            ->orderBy('id')
            ->limit(3)
            ->get();

        $data = [
            // ===== IN =====
            ['consumable' => $mouse, 'user' => null, 'type' => 'in', 'qty' => 50, 'days_ago' => 90, 'requested' => $admin, 'approved' => $admin, 'location' => null, 'asset' => null, 'purpose' => 'Pembelian awal stok', 'notes' => 'PO-2026-MSE-001'],
            ['consumable' => $keyboard, 'user' => null, 'type' => 'in', 'qty' => 30, 'days_ago' => 90, 'requested' => $admin, 'approved' => $admin, 'location' => null, 'asset' => null, 'purpose' => 'Pembelian awal stok', 'notes' => 'PO-2026-KBD-001'],
            ['consumable' => $hdd, 'user' => null, 'type' => 'in', 'qty' => 10, 'days_ago' => 85, 'requested' => $admin, 'approved' => $admin, 'location' => null, 'asset' => null, 'purpose' => 'Pembelian awal stok', 'notes' => 'PO-2026-HDD-001'],

            // ===== OUT =====
            ['consumable' => $mouse, 'user' => $budi, 'type' => 'out', 'qty' => 1, 'days_ago' => 60, 'requested' => $budi, 'approved' => $admin, 'location' => $budi?->location_id, 'asset' => $laptops[0] ?? null, 'purpose' => 'Mouse laptop rusak', 'notes' => null],
            ['consumable' => $mouse, 'user' => $siti, 'type' => 'out', 'qty' => 1, 'days_ago' => 45, 'requested' => $siti, 'approved' => $admin, 'location' => $siti?->location_id, 'asset' => null, 'purpose' => 'Mouse baru untuk staff', 'notes' => null],
            ['consumable' => $keyboard, 'user' => $andi, 'type' => 'out', 'qty' => 1, 'days_ago' => 40, 'requested' => $andi, 'approved' => $admin, 'location' => $andi?->location_id, 'asset' => $laptops[1] ?? null, 'purpose' => 'Keyboard rusak kena cairan', 'notes' => null],
            ['consumable' => $hdd, 'user' => $rudi, 'type' => 'out', 'qty' => 1, 'days_ago' => 30, 'requested' => $rudi, 'approved' => $admin, 'location' => $rudi?->location_id, 'asset' => null, 'purpose' => 'Backup data project', 'notes' => null],
            ['consumable' => $mouse, 'user' => $rudi, 'type' => 'out', 'qty' => 2, 'days_ago' => 20, 'requested' => $rudi, 'approved' => $admin, 'location' => $rudi?->location_id, 'asset' => null, 'purpose' => 'Mouse cadangan untuk tim IT', 'notes' => null],

            // ===== RETURN =====
            ['consumable' => $keyboard, 'user' => $siti, 'type' => 'return', 'qty' => 1, 'days_ago' => 15, 'requested' => $siti, 'approved' => $admin, 'location' => $siti?->location_id, 'asset' => null, 'purpose' => 'Keyboard tidak jadi dipakai', 'notes' => 'Kondisi masih bagus'],
        ];

        $inCount = $outCount = $returnCount = 0;

        foreach ($data as $d) {
            ConsumableTransaction::create([
                'consumable_id' => $d['consumable']->id,
                'user_id' => $d['user']?->id,
                'type' => $d['type'],
                'quantity' => $d['qty'],
                'transaction_date' => now()->subDays($d['days_ago']),
                'requested_by' => $d['requested']?->id,
                'approved_by' => $d['approved']?->id,
                'location_id' => $d['location'],
                'asset_id' => $d['asset']?->id,
                'purpose' => $d['purpose'],
                'notes' => $d['notes'],
            ]);

            $consumable = $d['consumable'];
            match ($d['type']) {
                'in' => $consumable->increment('stock_available', $d['qty']),
                'out' => $consumable->decrement('stock_available', $d['qty']),
                'return' => $consumable->increment('stock_available', $d['qty']),
            };

            match ($d['type']) {
                'in' => $inCount++,
                'out' => $outCount++,
                'return' => $returnCount++,
            };
        }

        $this->command->info("✅ Consumable Transactions: in={$inCount}, out={$outCount}, return={$returnCount}");
    }
}

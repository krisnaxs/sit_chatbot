<?php

namespace Database\Seeders;

use App\Models\Asset;
use App\Models\AssetAssignment;
use App\Models\AssetCategory;
use App\Models\AssetOwnership;
use App\Models\Consumable;
use App\Models\Department;
use App\Models\Location;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SiamSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedDepartments();
        $this->seedLocations();
        $this->seedUsers();
        $vendors = $this->seedVendors();
        $categories = $this->seedCategories();
        $this->seedAssets($categories, $vendors);
        $this->seedConsumables($categories);
        $this->seedAssignments();

        $this->command->newLine();
        $this->command->info('🎉 SIAM seeder selesai!');
    }

    // ================================================================
    private function seedDepartments(): void
    {
        $data = [
            ['name' => 'Information Technology', 'code' => 'IT'],
            ['name' => 'Finance', 'code' => 'FIN'],
            ['name' => 'Human Resource', 'code' => 'HRD'],
            ['name' => 'Marketing', 'code' => 'MKT'],
            ['name' => 'Operations', 'code' => 'OPS'],
        ];

        foreach ($data as $d) {
            Department::updateOrCreate(['code' => $d['code']], $d);
        }

        $this->command->info('✅ Departments: ' . count($data));
    }

    // ================================================================
    private function seedLocations(): void
    {
        $data = [
            ['building' => 'Gedung A', 'floor' => 'Lt. 1', 'room' => 'Ruang IT', 'division' => 'IT'],
            ['building' => 'Gedung A', 'floor' => 'Lt. 2', 'room' => 'Ruang Finance', 'division' => 'Finance'],
            ['building' => 'Gedung A', 'floor' => 'Lt. 2', 'room' => 'Ruang HRD', 'division' => 'HRD'],
            ['building' => 'Gedung B', 'floor' => 'Lt. 1', 'room' => 'Ruang Meeting', 'division' => 'General'],
            ['building' => 'Gedung B', 'floor' => 'Lt. 3', 'room' => 'Ruang Marketing', 'division' => 'Marketing'],
        ];

        foreach ($data as $d) {
            $d['full_name'] = "{$d['building']} - {$d['floor']} - {$d['room']}";
            Location::updateOrCreate(
                ['building' => $d['building'], 'floor' => $d['floor'], 'room' => $d['room']],
                $d
            );
        }

        $this->command->info('✅ Locations: ' . count($data));
    }

    // ================================================================
    private function seedUsers(): void
    {
        $itDept = Department::where('code', 'IT')->first();
        $finDept = Department::where('code', 'FIN')->first();
        $hrdDept = Department::where('code', 'HRD')->first();

        $itRoom = Location::where('room', 'Ruang IT')->first();
        $finRoom = Location::where('room', 'Ruang Finance')->first();
        $hrdRoom = Location::where('room', 'Ruang HRD')->first();

        // Update admin & support biar punya dept/lokasi
        User::where('email', 'admin@admin.com')->update([
            'department_id' => $itDept?->id,
            'location_id' => $itRoom?->id,
        ]);
        User::where('email', 'support@admin.com')->update([
            'department_id' => $itDept?->id,
            'location_id' => $itRoom?->id,
        ]);

        $users = [
            ['nip' => '20000001', 'name' => 'Budi Santoso', 'email' => 'budi.santoso@perusahaan.com', 'dept' => $itDept, 'loc' => $itRoom, 'position' => 'IT Support'],
            ['nip' => '20000002', 'name' => 'Siti Aminah', 'email' => 'siti.aminah@perusahaan.com', 'dept' => $finDept, 'loc' => $finRoom, 'position' => 'Staff Finance'],
            ['nip' => '20000003', 'name' => 'Andi Wijaya', 'email' => 'andi.wijaya@perusahaan.com', 'dept' => $hrdDept, 'loc' => $hrdRoom, 'position' => 'HRD Staff'],
            ['nip' => '20000004', 'name' => 'Dewi Lestari', 'email' => 'dewi.lestari@perusahaan.com', 'dept' => $finDept, 'loc' => $finRoom, 'position' => 'Finance Manager'],
            ['nip' => '20000005', 'name' => 'Rudi Hartono', 'email' => 'rudi.hartono@perusahaan.com', 'dept' => $itDept, 'loc' => $itRoom, 'position' => 'Programmer'],
        ];

        foreach ($users as $u) {
            // ✅ Generate username manual dari email (sebelum @)
            $username = User::generateUsername($u['email']);

            User::updateOrCreate(
                ['email' => $u['email']],
                [
                    'nip' => $u['nip'],
                    'username' => $username,          // ✅ WAJIB DIISI
                    'name' => $u['name'],
                    'password' => Hash::make('password123'),
                    'role' => 'user',
                    'is_active' => true,
                    'email_verified_at' => now(),
                    'department_id' => $u['dept']?->id,
                    'location_id' => $u['loc']?->id,
                    'position' => $u['position'],
                ]
            );
        }

        $this->command->info('✅ Users: ' . count($users) . ' pegawai');
    }

    // ================================================================
    private function seedVendors(): array
    {
        $data = [
            ['name' => 'PT Sewa Komputer Indonesia', 'type' => 'sewa', 'contact_person' => 'Bpk. Joko', 'phone' => '021-5550001'],
            ['name' => 'PT Lenovo Indonesia', 'type' => 'pembelian', 'contact_person' => 'Ibu Rina', 'phone' => '021-5550002'],
            ['name' => 'CV Mitra Office Supply', 'type' => 'both', 'contact_person' => 'Bpk. Anton', 'phone' => '021-5550003'],
        ];

        $vendors = [];
        foreach ($data as $v) {
            $vendors[$v['name']] = Vendor::updateOrCreate(['name' => $v['name']], $v);
        }

        $this->command->info('✅ Vendors: ' . count($vendors));
        return $vendors;
    }

    // ================================================================
    private function seedCategories(): array
    {
        $data = [
            ['name' => 'Laptop', 'code' => 'LPT', 'is_consumable' => false],
            ['name' => 'PC Desktop', 'code' => 'PC', 'is_consumable' => false],
            ['name' => 'Printer', 'code' => 'PRN', 'is_consumable' => false],
            ['name' => 'Monitor', 'code' => 'MON', 'is_consumable' => false],
            ['name' => 'Mouse', 'code' => 'MSE', 'is_consumable' => true],
            ['name' => 'Keyboard', 'code' => 'KBD', 'is_consumable' => true],
            ['name' => 'Hardisk', 'code' => 'HDD', 'is_consumable' => true],
        ];

        $categories = [];
        foreach ($data as $c) {
            $categories[$c['code']] = AssetCategory::updateOrCreate(['code' => $c['code']], $c);
        }

        $this->command->info('✅ Categories: ' . count($categories));
        return $categories;
    }

    // ================================================================
    private function seedAssets(array $categories, array $vendors): void
    {
        $lenovoVendor = $vendors['PT Lenovo Indonesia'];
        $sewaVendor = $vendors['PT Sewa Komputer Indonesia'];

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
                'category_id' => $categories['LPT']->id,
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
                'vendor_id' => $isLeased ? $sewaVendor->id : $lenovoVendor->id,
                'ownership_type' => $asset->ownership_type,
                'purchase_price' => $isLeased ? null : $asset->purchase_price,
                'invoice_number' => $isLeased ? null : 'INV-2026-' . str_pad($i, 4, '0', STR_PAD_LEFT),
                'contract_number' => $isLeased ? 'SEWA-2026-' . str_pad($i, 4, '0', STR_PAD_LEFT) : null,
                'contract_start' => $isLeased ? now()->subMonths(6) : null,
                'contract_end' => $isLeased ? now()->addMonths(18) : null,
                'monthly_cost' => $isLeased ? 500000 : null,
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
                'category_id' => $categories['PC']->id,
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
                'vendor_id' => $lenovoVendor->id,
                'ownership_type' => 'owned',
                'purchase_price' => 8500000,
                'invoice_number' => 'INV-PC-2026-' . str_pad($i, 4, '0', STR_PAD_LEFT),
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
                'category_id' => $categories['PRN']->id,
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
                'vendor_id' => $isLeased ? $sewaVendor->id : $lenovoVendor->id,
                'ownership_type' => $asset->ownership_type,
                'purchase_price' => $isLeased ? null : 3500000,
                'monthly_cost' => $isLeased ? 200000 : null,
                'contract_start' => $isLeased ? now()->subMonths(3) : null,
                'contract_end' => $isLeased ? now()->addMonths(9) : null,
            ]);
        }

        $this->command->info("✅ Assets: {$counter} unit (40 T14, 10 PC, 5 Printer)");
    }

    // ================================================================
    private function seedConsumables(array $categories): void
    {
        $data = [
            [
                'name' => 'Mouse Logitech M170',
                'category_id' => $categories['MSE']->id,
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
                'category_id' => $categories['KBD']->id,
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
                'category_id' => $categories['HDD']->id,
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
    private function seedAssignments(): void
    {
        $admin = User::where('email', 'admin@admin.com')->first();
        $support = User::where('email', 'support@admin.com')->first();
        $budi = User::where('email', 'budi.santoso@perusahaan.com')->first();
        $siti = User::where('email', 'siti.aminah@perusahaan.com')->first();
        $andi = User::where('email', 'andi.wijaya@perusahaan.com')->first();
        $dewi = User::where('email', 'dewi.lestari@perusahaan.com')->first();
        $rudi = User::where('email', 'rudi.hartono@perusahaan.com')->first();

        $laptops = Asset::where('model', 'ThinkPad T14 Gen 4')
            ->orderBy('id')
            ->limit(10)
            ->get();

        $assignments = [
            ['asset' => $laptops[0] ?? null, 'user' => $budi, 'days_ago' => 180, 'returned' => null],
            ['asset' => $laptops[1] ?? null, 'user' => $siti, 'days_ago' => 150, 'returned' => null],
            ['asset' => $laptops[2] ?? null, 'user' => $andi, 'days_ago' => 120, 'returned' => null],
            ['asset' => $laptops[3] ?? null, 'user' => $dewi, 'days_ago' => 90, 'returned' => null],
            ['asset' => $laptops[4] ?? null, 'user' => $rudi, 'days_ago' => 60, 'returned' => null],
            ['asset' => $laptops[5] ?? null, 'user' => $support, 'days_ago' => 200, 'returned' => 30],
            ['asset' => $laptops[6] ?? null, 'user' => $dewi, 'days_ago' => 300, 'returned' => 150],
            ['asset' => $laptops[6] ?? null, 'user' => $budi, 'days_ago' => 150, 'returned' => 30],
        ];

        foreach ($assignments as $a) {
            if (!$a['asset'] || !$a['user']) {
                continue;
            }

            $assignedAt = now()->subDays($a['days_ago']);
            $returnedAt = $a['returned'] ? now()->subDays($a['returned']) : null;

            AssetAssignment::create([
                'asset_id' => $a['asset']->id,
                'user_id' => $a['user']->id,
                'location_id' => $a['user']->location_id,
                'department_id' => $a['user']->department_id,
                'assigned_at' => $assignedAt,
                'returned_at' => $returnedAt,
                'assigned_by' => $admin?->id,
                'received_by' => $a['user']->id,
                'condition_on_assign' => 100,
                'condition_on_return' => $returnedAt ? rand(70, 95) : null,
                'notes' => 'BAST-2026-' . str_pad(rand(1, 999), 4, '0', STR_PAD_LEFT),
            ]);

            if ($returnedAt === null) {
                $a['asset']->update([
                    'status' => 'in_use',
                    'current_user_id' => $a['user']->id,
                    'current_location_id' => $a['user']->location_id,
                ]);
            }
        }

        $this->command->info('✅ Assignments: ' . count($assignments) . ' records');
    }
}

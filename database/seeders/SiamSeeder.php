<?php

namespace Database\Seeders;

use App\Models\Asset;
use App\Models\AssetAssignment;
use App\Models\AssetCategory;
use App\Models\AssetLoan;
use App\Models\AssetMaintenance;
use App\Models\AssetMovement;
use App\Models\AssetOwnership;
use App\Models\AssetType;
use App\Models\Consumable;
use App\Models\ConsumableTransaction;
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
        $this->seedAssetTypes();       // tetap dijalankan → untuk dropdown
        $this->seedAssets($categories, $vendors);
        $this->seedConsumables($categories);
        $this->seedAssignments();
        $this->seedLoans();
        $this->seedMaintenances();
        $this->seedMovements();
        $this->seedConsumableTransactions();

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
            $username = User::generateUsername($u['email']);

            User::updateOrCreate(
                ['email' => $u['email']],
                [
                    'nip' => $u['nip'],
                    'username' => $username,
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
    // Master data untuk dropdown form (tidak direlasikan ke assets)
    private function seedAssetTypes(): void
    {
        $data = [
            ['brand' => 'Lenovo', 'model' => 'ThinkPad T14 Gen 4', 'description' => 'Laptop bisnis 14 inch'],
            ['brand' => 'HP', 'model' => 'ProDesk 400 G9', 'description' => 'PC Desktop SFF'],
            ['brand' => 'Epson', 'model' => 'L3110 All-in-One', 'description' => 'Printer inkjet 3in1'],
            ['brand' => 'Logitech', 'model' => 'M170', 'description' => 'Mouse wireless'],
            ['brand' => 'Logitech', 'model' => 'K120', 'description' => 'Keyboard USB'],
            ['brand' => 'Seagate', 'model' => 'Expansion 1TB', 'description' => 'HDD eksternal'],
        ];

        foreach ($data as $d) {
            AssetType::updateOrCreate(
                ['brand' => $d['brand'], 'model' => $d['model']],
                $d
            );
        }

        $this->command->info('✅ Asset Types: ' . count($data));
    }

    // ================================================================
    private function seedAssets(array $categories, array $vendors): void
    {
        $lenovoVendor = $vendors['PT Lenovo Indonesia'];
        $sewaVendor = $vendors['PT Sewa Komputer Indonesia'];
        $mitraVendor = $vendors['CV Mitra Office Supply'];

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
                'vendor_id' => $mitraVendor->id,
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
                'vendor_id' => $isLeased ? $sewaVendor->id : $mitraVendor->id,
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

        $laptops = Asset::where('hostname', 'like', 'NB-T14-%')
            ->orderBy('id')
            ->limit(10)
            ->get();

        if ($laptops->count() < 7) {
            $this->command->warn('⚠️  Laptop T14 kurang dari 7 unit — assignments dilewati.');
            return;
        }

        $assignments = [
            ['asset' => $laptops[0], 'user' => $budi, 'days_ago' => 180, 'returned' => null],
            ['asset' => $laptops[1], 'user' => $siti, 'days_ago' => 150, 'returned' => null],
            ['asset' => $laptops[2], 'user' => $andi, 'days_ago' => 120, 'returned' => null],
            ['asset' => $laptops[3], 'user' => $dewi, 'days_ago' => 90, 'returned' => null],
            ['asset' => $laptops[4], 'user' => $rudi, 'days_ago' => 60, 'returned' => null],
            ['asset' => $laptops[5], 'user' => $support, 'days_ago' => 200, 'returned' => 30],
            ['asset' => $laptops[6], 'user' => $dewi, 'days_ago' => 300, 'returned' => 150],
            ['asset' => $laptops[6], 'user' => $budi, 'days_ago' => 150, 'returned' => 30],
        ];

        foreach ($assignments as $a) {
            if (!$a['asset'] || !$a['user']) {
                continue;
            }

            $assignedAt = now()->subDays($a['days_ago']);
            $returnedAt = $a['returned'] ? now()->subDays($a['returned']) : null;

            AssetAssignment::create([
                'asset_id' => $a['asset']->id,
                'hostname' => $a['asset']->hostname,
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

    // ================================================================
    private function seedLoans(): void
    {
        $admin = User::where('email', 'admin@admin.com')->first();
        $siti = User::where('email', 'siti.aminah@perusahaan.com')->first();
        $andi = User::where('email', 'andi.wijaya@perusahaan.com')->first();
        $dewi = User::where('email', 'dewi.lestari@perusahaan.com')->first();

        $loanableAssets = Asset::where('hostname', 'like', 'NB-T14-%')
            ->whereNotIn('id', function ($q) {
                $q->select('asset_id')->from('asset_assignments')->whereNull('returned_at');
            })
            ->whereNotIn('id', function ($q) {
                $q->select('asset_id')->from('asset_loans')
                    ->whereIn('status', ['approved', 'borrowed', 'overdue']);
            })
            ->orderBy('id')
            ->limit(4)
            ->get();

        if ($loanableAssets->count() < 4) {
            $this->command->warn('⚠️  Asset untuk loan kurang — loans dilewati.');
            return;
        }

        $data = [
            [
                'asset' => $loanableAssets[0],
                'user' => $siti,
                'loan' => 20,
                'due' => 5,
                'returned' => null,
                'status' => 'borrowed',
                'purpose' => 'Kunjungan klien',
            ],
            [
                'asset' => $loanableAssets[1],
                'user' => $andi,
                'loan' => 15,
                'due' => -3,
                'returned' => null,
                'status' => 'overdue',
                'purpose' => 'Pelatihan HRD',
            ],
            [
                'asset' => $loanableAssets[2],
                'user' => $dewi,
                'loan' => 30,
                'due' => 10,
                'returned' => null,
                'status' => 'approved',
                'purpose' => 'Audit laporan keuangan',
            ],
            [
                'asset' => $loanableAssets[3],
                'user' => $siti,
                'loan' => 60,
                'due' => 30,
                'returned' => 25,
                'status' => 'returned',
                'purpose' => 'Meeting bulanan',
            ],
        ];

        foreach ($data as $d) {
            AssetLoan::create([
                'asset_id' => $d['asset']->id,
                'user_id' => $d['user']->id,
                'loan_date' => now()->subDays($d['loan']),
                'due_date' => now()->addDays($d['due']),
                'returned_at' => $d['returned'] ? now()->subDays($d['returned']) : null,
                'purpose' => $d['purpose'],
                'approved_by' => $admin?->id,
                'status' => $d['status'],
                'condition_on_loan' => 100,
                'condition_on_return' => $d['returned'] ? rand(80, 95) : null,
                'notes' => 'Loan-' . strtoupper($d['status']),
            ]);

            if (in_array($d['status'], ['approved', 'borrowed', 'overdue'])) {
                $d['asset']->update([
                    'status' => 'loaned',
                    'current_user_id' => $d['user']->id,
                ]);
            }
        }

        $this->command->info('✅ Loans: ' . count($data) . ' records');
    }

    // ================================================================
    private function seedMaintenances(): void
    {
        $mitraVendor = Vendor::where('name', 'CV Mitra Office Supply')->first();
        $sewaVendor = Vendor::where('name', 'PT Sewa Komputer Indonesia')->first();

        $assets = Asset::where('hostname', 'like', 'NB-T14-%')
            ->orderBy('id')
            ->limit(5)
            ->get();

        if ($assets->count() < 5) {
            $this->command->warn('⚠️  Asset untuk maintenance kurang — dilewati.');
            return;
        }

        $data = [
            [
                'asset' => $assets[0],
                'vendor' => $mitraVendor,
                'type' => 'corrective',
                'issue' => 'Keyboard beberapa tombol tidak berfungsi',
                'action' => 'Ganti keyboard baru',
                'tech' => 'Bpk. Slamet',
                'cost' => 350000,
                'start' => 60,
                'end' => 58,
                'status' => 'done',
                'cond_b' => 70,
                'cond_a' => 95,
            ],
            [
                'asset' => $assets[1],
                'vendor' => $sewaVendor,
                'type' => 'preventive',
                'issue' => 'Pembersihan rutin dan update BIOS',
                'action' => 'Clean up + BIOS update',
                'tech' => 'Tim Vendor Sewa',
                'cost' => 0,
                'start' => 30,
                'end' => 30,
                'status' => 'done',
                'cond_b' => 85,
                'cond_a' => 98,
            ],
            [
                'asset' => $assets[2],
                'vendor' => $mitraVendor,
                'type' => 'corrective',
                'issue' => 'Baterai cepat habis (drop < 30 menit)',
                'action' => 'Menunggu penggantian baterai dari vendor',
                'tech' => 'Bpk. Slamet',
                'cost' => 850000,
                'start' => 5,
                'end' => null,
                'status' => 'in_progress',
                'cond_b' => 60,
                'cond_a' => null,
            ],
            [
                'asset' => $assets[3],
                'vendor' => $mitraVendor,
                'type' => 'upgrade',
                'issue' => 'RAM 16GB kurang untuk workload developer',
                'action' => 'Upgrade ke 32GB DDR4',
                'tech' => 'Tim IT Internal',
                'cost' => 1200000,
                'start' => 90,
                'end' => 88,
                'status' => 'done',
                'cond_b' => 80,
                'cond_a' => 100,
            ],
            [
                'asset' => $assets[4],
                'vendor' => $sewaVendor,
                'type' => 'corrective',
                'issue' => 'Layar berkedip-kedip',
                'action' => 'Pengecekan kabel fleksibel LCD',
                'tech' => 'Tim Vendor Sewa',
                'cost' => 0,
                'start' => 2,
                'end' => null,
                'status' => 'open',
                'cond_b' => 65,
                'cond_a' => null,
            ],
        ];

        foreach ($data as $d) {
            AssetMaintenance::create([
                'asset_id' => $d['asset']->id,
                'vendor_id' => $d['vendor']?->id,
                'type' => $d['type'],
                'issue' => $d['issue'],
                'action' => $d['action'],
                'technician' => $d['tech'],
                'cost' => $d['cost'],
                'start_date' => now()->subDays($d['start']),
                'end_date' => $d['end'] !== null ? now()->subDays($d['end']) : null,
                'status' => $d['status'],
                'condition_before' => $d['cond_b'],
                'condition_after' => $d['cond_a'],
                'notes' => 'Maintenance ' . $d['type'],
            ]);

            if (in_array($d['status'], ['open', 'in_progress'])) {
                $d['asset']->update(['status' => 'maintenance']);
            }
        }

        $this->command->info('✅ Maintenances: ' . count($data) . ' records');
    }

    // ================================================================
    private function seedMovements(): void
    {
        $admin = User::where('email', 'admin@admin.com')->first();

        $assignments = AssetAssignment::with('asset', 'user')
            ->orderBy('id')
            ->limit(8)
            ->get();

        if ($assignments->isEmpty()) {
            $this->command->warn('⚠️  Tidak ada assignment — movements dilewati.');
            return;
        }

        foreach ($assignments as $a) {
            AssetMovement::create([
                'asset_id' => $a->asset_id,
                'movable_type' => User::class,
                'movable_id' => $a->user_id,
                'from_location_id' => null,
                'to_location_id' => $a->location_id,
                'type' => 'assign',
                'reference_table' => 'asset_assignments',
                'reference_id' => $a->id,
                'moved_at' => $a->assigned_at,
                'moved_by' => $admin?->id,
                'notes' => "Assign ke {$a->user?->name}",
            ]);

            if ($a->returned_at) {
                AssetMovement::create([
                    'asset_id' => $a->asset_id,
                    'movable_type' => User::class,
                    'movable_id' => $a->user_id,
                    'from_location_id' => $a->location_id,
                    'to_location_id' => null,
                    'type' => 'return',
                    'reference_table' => 'asset_assignments',
                    'reference_id' => $a->id,
                    'moved_at' => $a->returned_at,
                    'moved_by' => $admin?->id,
                    'notes' => "Return dari {$a->user?->name}",
                ]);
            }
        }

        $loans = AssetLoan::whereIn('status', ['approved', 'borrowed', 'overdue'])
            ->orderBy('id')
            ->get();

        foreach ($loans as $loan) {
            AssetMovement::create([
                'asset_id' => $loan->asset_id,
                'movable_type' => User::class,
                'movable_id' => $loan->user_id,
                'from_location_id' => null,
                'to_location_id' => null,
                'type' => 'loan',
                'reference_table' => 'asset_loans',
                'reference_id' => $loan->id,
                'moved_at' => $loan->loan_date,
                'moved_by' => $admin?->id,
                'notes' => "Loan: {$loan->purpose}",
            ]);
        }

        $maintenances = AssetMaintenance::orderBy('id')->get();

        foreach ($maintenances as $m) {
            AssetMovement::create([
                'asset_id' => $m->asset_id,
                'movable_type' => null,
                'movable_id' => null,
                'from_location_id' => null,
                'to_location_id' => null,
                'type' => 'maintenance',
                'reference_table' => 'asset_maintenances',
                'reference_id' => $m->id,
                'moved_at' => $m->start_date,
                'moved_by' => $admin?->id,
                'notes' => "Maintenance: {$m->issue}",
            ]);
        }

        $this->command->info('✅ Movements: generated');
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

            // Update stock real-time
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

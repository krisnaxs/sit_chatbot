<?php

namespace Database\Seeders;

use App\Models\AssetCategory;
use App\Models\AssetType;
use App\Models\Department;
use App\Models\Location;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class MasterDataSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedDepartments();
        $this->seedLocations();
        $this->seedUsers();
        $this->seedVendors();
        $this->seedCategories();
        $this->seedAssetTypes();
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
    private function seedVendors(): void
    {
        $data = [
            ['name' => 'PT Sewa Komputer Indonesia', 'type' => 'sewa', 'contact_person' => 'Bpk. Joko', 'phone' => '021-5550001'],
            ['name' => 'PT Lenovo Indonesia', 'type' => 'pembelian', 'contact_person' => 'Ibu Rina', 'phone' => '021-5550002'],
            ['name' => 'CV Mitra Office Supply', 'type' => 'both', 'contact_person' => 'Bpk. Anton', 'phone' => '021-5550003'],
        ];

        foreach ($data as $v) {
            Vendor::updateOrCreate(['name' => $v['name']], $v);
        }

        $this->command->info('✅ Vendors: ' . count($data));
    }

    // ================================================================
    private function seedCategories(): void
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

        foreach ($data as $c) {
            AssetCategory::updateOrCreate(['code' => $c['code']], $c);
        }

        $this->command->info('✅ Categories: ' . count($data));
    }

    // ================================================================
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
}

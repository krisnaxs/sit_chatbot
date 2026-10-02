<?php

namespace Database\Seeders;

use App\Models\Vendor;
use Illuminate\Database\Seeder;

class VendorSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            ['name' => 'PT Sewa Komputer Indonesia', 'type' => 'sewa', 'contact_person' => 'Bpk. Joko', 'phone' => '021-5550001'],
            ['name' => 'PT Lenovo Indonesia', 'type' => 'pembelian', 'contact_person' => 'Ibu Rina', 'phone' => '021-5550002'],
            ['name' => 'CV Mitra Office Supply', 'type' => 'both', 'contact_person' => 'Bpk. Anton', 'phone' => '021-5550003'],
        ];

        foreach ($data as $v) {
            Vendor::updateOrCreate(['name' => $v['name']], $v);
        }

        $this->command->info(' Vendors: ' . count($data));
    }
}

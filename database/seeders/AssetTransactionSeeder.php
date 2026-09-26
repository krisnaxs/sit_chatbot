<?php

namespace Database\Seeders;

use App\Models\Asset;
use App\Models\AssetAssignment;
use App\Models\AssetLoan;
use App\Models\AssetMaintenance;
use App\Models\AssetMovement;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Database\Seeder;

class AssetTransactionSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedAssignments();
        $this->seedLoans();
        $this->seedMaintenances();
        $this->seedMovements();
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
            ['asset' => $loanableAssets[0], 'user' => $siti, 'loan' => 20, 'due' => 5, 'returned' => null, 'status' => 'borrowed', 'purpose' => 'Kunjungan klien'],
            ['asset' => $loanableAssets[1], 'user' => $andi, 'loan' => 15, 'due' => -3, 'returned' => null, 'status' => 'overdue', 'purpose' => 'Pelatihan HRD'],
            ['asset' => $loanableAssets[2], 'user' => $dewi, 'loan' => 30, 'due' => 10, 'returned' => null, 'status' => 'approved', 'purpose' => 'Audit laporan keuangan'],
            ['asset' => $loanableAssets[3], 'user' => $siti, 'loan' => 60, 'due' => 30, 'returned' => 25, 'status' => 'returned', 'purpose' => 'Meeting bulanan'],
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
            ['asset' => $assets[0], 'vendor' => $mitraVendor, 'type' => 'corrective', 'issue' => 'Keyboard beberapa tombol tidak berfungsi', 'action' => 'Ganti keyboard baru', 'tech' => 'Bpk. Slamet', 'cost' => 350000, 'start' => 60, 'end' => 58, 'status' => 'done', 'cond_b' => 70, 'cond_a' => 95],
            ['asset' => $assets[1], 'vendor' => $sewaVendor, 'type' => 'preventive', 'issue' => 'Pembersihan rutin dan update BIOS', 'action' => 'Clean up + BIOS update', 'tech' => 'Tim Vendor Sewa', 'cost' => 0, 'start' => 30, 'end' => 30, 'status' => 'done', 'cond_b' => 85, 'cond_a' => 98],
            ['asset' => $assets[2], 'vendor' => $mitraVendor, 'type' => 'corrective', 'issue' => 'Baterai cepat habis (drop < 30 menit)', 'action' => 'Menunggu penggantian baterai dari vendor', 'tech' => 'Bpk. Slamet', 'cost' => 850000, 'start' => 5, 'end' => null, 'status' => 'in_progress', 'cond_b' => 60, 'cond_a' => null],
            ['asset' => $assets[3], 'vendor' => $mitraVendor, 'type' => 'upgrade', 'issue' => 'RAM 16GB kurang untuk workload developer', 'action' => 'Upgrade ke 32GB DDR4', 'tech' => 'Tim IT Internal', 'cost' => 1200000, 'start' => 90, 'end' => 88, 'status' => 'done', 'cond_b' => 80, 'cond_a' => 100],
            ['asset' => $assets[4], 'vendor' => $sewaVendor, 'type' => 'corrective', 'issue' => 'Layar berkedip-kedip', 'action' => 'Pengecekan kabel fleksibel LCD', 'tech' => 'Tim Vendor Sewa', 'cost' => 0, 'start' => 2, 'end' => null, 'status' => 'open', 'cond_b' => 65, 'cond_a' => null],
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
}

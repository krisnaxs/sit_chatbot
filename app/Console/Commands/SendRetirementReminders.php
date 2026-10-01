<?php

namespace App\Console\Commands;

use App\Models\Asset;
use App\Models\User;
use App\Notifications\RetirementReminderNotification;
use App\Services\SettingsService;
use App\Services\WhatsApp\WhatsAppService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class SendRetirementReminders extends Command
{
    protected $signature = 'assets:retirement-reminder {--days=30}';
    protected $description = 'Kirim reminder pensiun via Email & WhatsApp ke pegawai + admin';

    public function handle(WhatsAppService $wa): int
    {
        $days = (int) $this->option('days');

        $this->info("Mulai kirim reminder (rentang: {$days} hari)...");
        $admins = User::whereIn('role', ['admin', 'support'])
            ->whereNotNull('email')
            ->get();
        $fallbackNumbers = array_filter(array_map(
            'trim',
            explode(',', SettingsService::get('whatsapp', 'admin_numbers', '') ?? '')
        ));

        $adminWaNumbers = User::whereIn('role', ['admin', 'support'])
            ->whereNotNull('phone')
            ->pluck('phone')
            ->merge($fallbackNumbers)
            ->filter()
            ->unique()
            ->values()
            ->toArray();

        $this->line("Admin email  : " . $admins->count());
        $this->line("Admin WA     : " . count($adminWaNumbers));
        $this->newLine();
        $akanPensiun = Asset::akanDitarik($days)
            ->with(['currentUser', 'category'])
            ->get();

        $this->info("▶ Akan pensiun: {$akanPensiun->count()} aset");
        foreach ($akanPensiun as $asset) {
            $this->dispatch($asset, 'akan_pensiun', $admins, $adminWaNumbers, $wa);
        }
        $sudahPensiun = Asset::dipegangPensiunan()
            ->with(['currentUser', 'category'])
            ->get();

        $this->info("▶ Sudah pensiun: {$sudahPensiun->count()} aset");
        foreach ($sudahPensiun as $asset) {
            $this->dispatch($asset, 'sudah_pensiun', $admins, $adminWaNumbers, $wa);
        }

        $this->newLine();
        $this->info('✓ Selesai.');

        return self::SUCCESS;
    }

    protected function dispatch(
        Asset $asset,
        string $type,
        $admins,
        array $adminWaNumbers,
        WhatsAppService $wa
    ): void {
        $employee = $asset->currentUser;

        if (!$employee) {
            $this->warn("  ⚠ Aset #{$asset->id} tidak punya pemegang, dilewati.");
            return;
        }

        $notifEmployee = new RetirementReminderNotification($asset, $type, sendToAdmin: false);
        $notifAdmin = new RetirementReminderNotification($asset, $type, sendToAdmin: true);
        if ($employee->email) {
            Notification::send($employee, $notifEmployee);
            $this->line("  📧 Email → {$employee->name} <{$employee->email}>");
        }
        if ($admins->isNotEmpty()) {
            Notification::send($admins, $notifAdmin);
            $this->line("  📧 Email → {$admins->count()} admin");
        }
        if ($employee->phone) {
            $ok = $wa->send($employee->phone, $notifEmployee->buildEmployeeMessage());
            $this->line("  💬 WA    → {$employee->name} ({$employee->phone}) " . ($ok ? '✓' : '✗'));
        }
        foreach ($adminWaNumbers as $num) {
            $wa->send($num, $notifAdmin->buildAdminMessage());
        }
        if (!empty($adminWaNumbers)) {
            $this->line("  💬 WA    → " . count($adminWaNumbers) . " admin");
        }

        Log::info('Retirement reminder sent', [
            'asset_id' => $asset->id,
            'employee' => $employee->name,
            'type' => $type,
        ]);
    }
}

<?php

namespace App\Notifications;

use App\Mail\RetirementReminderMail;
use App\Models\Asset;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class RetirementReminderNotification extends Notification
{
    use Queueable;

    /**
     * @param  Asset   $asset        Aset yang dipegang pegawai
     * @param  string  $type         'akan_pensiun' | 'sudah_pensiun' | 'perlu_ditarik'
     * @param  bool    $sendToAdmin  true = notifikasi ini untuk admin (tidak CC admin lagi)
     */
    public function __construct(
        public Asset $asset,
        public string $type,
        public bool $sendToAdmin = false
    ) {
    }

    /**
     * Channel pengiriman.
     */
    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Email yang dikirim.
     * Kalau untuk pegawai → CC ke admin/support.
     */
    public function toMail($notifiable)
    {
        $mail = (new RetirementReminderMail($this->asset, $this->type))
            ->to($notifiable->email);
        if (!$this->sendToAdmin) {
            $cc = User::whereIn('role', ['admin', 'support'])
                ->whereNotNull('email')
                ->pluck('email')
                ->reject(fn($email) => $email === $notifiable->email) // hindari dobel
                ->unique()
                ->toArray();

            if (!empty($cc)) {
                $mail->cc($cc);
            }
        }

        return $mail;
    }

    /**
     * Data yang disimpan di tabel notifications (database channel).
     */
    public function toArray($notifiable): array
    {
        return [
            'asset_id' => $this->asset->id,
            'asset_code' => $this->asset->asset_code,
            'asset_name' => $this->asset->full_name,
            'user_id' => $this->asset->current_user_id,
            'user_name' => $this->asset->currentUser->name ?? '-',
            'type' => $this->type,
            'message' => $this->buildShortMessage(),
        ];
    }

    protected function buildShortMessage(): string
    {
        $u = $this->asset->currentUser;
        $a = $this->asset;

        return match ($this->type) {
            'akan_pensiun' => "⚠️ {$u->name} akan pensiun. Aset {$a->asset_code} perlu disiapkan pengembaliannya.",
            'sudah_pensiun' => "🚨 {$u->name} sudah pensiun tapi aset {$a->asset_code} belum dikembalikan!",
            'perlu_ditarik' => "📌 Aset {$a->asset_code} perlu ditarik dari {$u->name}.",
            default => 'Reminder pensiun.',
        };
    }

    /**
     * Pesan WA untuk PEGAWAI (bahasa personal).
     */
    public function buildEmployeeMessage(): string
    {
        $u = $this->asset->currentUser;
        $a = $this->asset;

        return match ($this->type) {
            'akan_pensiun' => "Halo Bpk/Ibu *{$u->name}*,\n\n"
                . "Masa pensiun Anda akan tiba pada *"
                . optional($u->waktu_pensiun)->translatedFormat('d F Y') . "*.\n\n"
                . "Mohon persiapan pengembalian aset kantor berikut:\n"
                . "• Aset: {$a->brand} {$a->model}\n"
                . "• Kode: " . ($a->asset_code ?? '-') . "\n"
                . "• S/N: {$a->serial_number}\n\n"
                . "Silakan koordinasi dengan Tim IT/HRD untuk proses serah terima.\n\n"
                . "Terima kasih atas dedikasi Anda. 🙏\n"
                . "_SIAM_",

            'sudah_pensiun' => "Halo Bpk/Ibu *{$u->name}*,\n\n"
                . "Selamat menikmati masa pensiun. 🙏\n\n"
                . "Kami catat masih ada aset kantor yang belum dikembalikan:\n"
                . "• Aset: {$a->brand} {$a->model}\n"
                . "• Kode: " . ($a->asset_code ?? '-') . "\n"
                . "• S/N: {$a->serial_number}\n\n"
                . "Mohon segera berkoordinasi dengan Tim IT/HRD untuk pengembalian.\n\n"
                . "_SIAM_",

            'perlu_ditarik' => "Halo Bpk/Ibu *{$u->name}*,\n\n"
                . "Aset kantor berikut perlu segera dikembalikan:\n"
                . "• Aset: {$a->brand} {$a->model}\n"
                . "• Kode: " . ($a->asset_code ?? '-') . "\n"
                . "• S/N: {$a->serial_number}\n\n"
                . "Mohon hubungi Tim IT.\n\n"
                . "_SIAM_",

            default => "Pengingat aset dari SIAM.",
        };
    }

    /**
     * Pesan WA untuk ADMIN (bahasa laporan).
     */
    public function buildAdminMessage(): string
    {
        $u = $this->asset->currentUser;
        $a = $this->asset;

        return match ($this->type) {
            'akan_pensiun' => "⚠️ *REMINDER PENSIUN - SIAM*\n\n"
                . "Pegawai: *{$u->name}*\n"
                . "NIP: " . ($u->nip ?? '-') . "\n"
                . "Pensiun: " . optional($u->waktu_pensiun)->translatedFormat('d F Y') . "\n"
                . "Sisa: " . now()->diffInDays($u->waktu_pensiun) . " hari\n\n"
                . "Aset yang dipegang:\n"
                . "• {$a->brand} {$a->model}\n"
                . "• Kode: " . ($a->asset_code ?? '-') . "\n"
                . "• S/N: {$a->serial_number}\n\n"
                . "Segera koordinasi pengembalian.",

            'sudah_pensiun' => "🚨 *URGENT - ASET BELUM DIKEMBALIKAN*\n\n"
                . "Pegawai: *{$u->name}* (SUDAH PENSIUN)\n"
                . "Tgl Pensiun: " . optional($u->waktu_pensiun)->translatedFormat('d F Y') . "\n"
                . "Lewat: " . now()->diffInDays($u->waktu_pensiun) . " hari\n\n"
                . "Aset:\n"
                . "• {$a->brand} {$a->model}\n"
                . "• Kode: " . ($a->asset_code ?? '-') . "\n"
                . "• S/N: {$a->serial_number}\n\n"
                . "SEGERA TARIK ASET!",

            'perlu_ditarik' => "📌 *ASET PERLU DITARIK*\n\n"
                . "Aset: {$a->brand} {$a->model}\n"
                . "Kode: " . ($a->asset_code ?? '-') . "\n"
                . "S/N: {$a->serial_number}\n"
                . "Pemegang: {$u->name}\n\n"
                . "Segera proses.",

            default => "Notifikasi aset dari SIAM.",
        };
    }
}

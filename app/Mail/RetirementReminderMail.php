<?php

namespace App\Mail;

use App\Models\Asset;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class RetirementReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Asset $asset,
        public string $type
    ) {
    }

    public function build()
    {
        $u = $this->asset->currentUser;
        $nama = $u->name ?? 'Pegawai';
        $tanggal = optional($u?->waktu_pensiun)->translatedFormat('d F Y') ?? '-';

        $subject = match ($this->type) {
            'akan_pensiun' => "[SIAM] Reminder: {$nama} akan pensiun {$tanggal}",
            'sudah_pensiun' => "[SIAM] URGENT: {$nama} sudah pensiun, aset belum dikembalikan",
            'perlu_ditarik' => "[SIAM] Aset perlu ditarik: " . ($this->asset->asset_code ?? '-'),
            default => "[SIAM] Reminder Aset",
        };

        return $this->subject($subject)
            ->view('emails.retirement-reminder');
    }
}

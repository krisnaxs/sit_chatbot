<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/**
 * Cleanup expired chat contexts setiap jam.
 * Menghapus konteks percakapan yang sudah lewat masa berlaku (default 30 menit).
 */
Schedule::call(function () {
    $deleted = \App\Services\ChatMemoryService::cleanup();

    if ($deleted > 0) {
        Log::info("Chat context cleanup: {$deleted} record dihapus");
    }
})->hourly()
    ->name('cleanup-chat-contexts')
    ->withoutOverlapping();

/**
 * Cleanup pending knowledge lama (> 30 hari, masih pending).
 * Opsional — biar tabel pending_knowledge tidak menumpuk.
 */
Schedule::call(function () {
    $deleted = \App\Models\PendingKnowledge::where('status', 'pending')
        ->where('created_at', '<', now()->subDays(30))
        ->delete();

    if ($deleted > 0) {
        Log::info("Pending knowledge cleanup: {$deleted} record dihapus");
    }
})->daily()
    ->name('cleanup-pending-knowledge')
    ->withoutOverlapping();

/**
 * Auto-approve learned FAQ yang sudah sering ditanya.
 * Kalau ada FAQ dengan frequency >= 3 tapi belum approved, approve otomatis.
 * Opsional — sebagai backup kalau AutoLearningService tidak jalan.
 */
Schedule::call(function () {
    $updated = \App\Models\LearnedFaq::where('is_approved', false)
        ->where('frequency', '>=', 3)
        ->where('confidence', '>=', 0.5)
        ->update([
            'is_approved' => true,
            'approved_at' => now(),
        ]);

    if ($updated > 0) {
        Log::info("Learned FAQ auto-approve: {$updated} FAQ disetujui");
    }
})->daily()
    ->name('auto-approve-learned-faqs')
    ->withoutOverlapping();

Schedule::command('assets:retirement-reminder --days=30')
    ->dailyAt('08:00')
    ->timezone('Asia/Jakarta')
    ->onOneServer()
    ->withoutOverlapping();

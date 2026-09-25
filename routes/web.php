<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PortalController;
use App\Http\Controllers\AppController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\KnowledgeController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\PendingKnowledgeController;
use App\Http\Controllers\UserController;

// ============================================================
// AUTH ADMIN
// ============================================================
Route::post('/admin/login', [LoginController::class, 'login'])->name('admin.login');
Route::post('/admin/logout', [LoginController::class, 'logout'])->name('admin.logout');

// Placeholder route login supaya middleware auth tidak error
Route::get('/login', function () {
    return redirect('/'); // Modal login ada di portal
})->name('login');

// ============================================================
// ROUTES YANG WAJIB LOGIN (ADMIN)
// ============================================================
Route::middleware(['auth'])->group(function () {

    // ---- Manajemen Aplikasi ----
    Route::get('/apps', [AppController::class, 'index'])->name('apps.index');
    Route::get('/apps/create', [AppController::class, 'create'])->name('apps.create');
    Route::post('/apps', [AppController::class, 'store'])->name('apps.store');
    Route::get('/apps/{app}/edit', [AppController::class, 'edit'])->name('apps.edit');
    Route::put('/apps/{app}', [AppController::class, 'update'])->name('apps.update');
    Route::delete('/apps/{app}', [AppController::class, 'destroy'])->name('apps.destroy');

    // ---- Dashboard ----
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // ============================================================
    // 🆕 PENDING KNOWLEDGE — WAJIB DI ATAS Resource knowledge
    // (hanya admin & support)
    // ============================================================
    Route::middleware(['role:admin,support'])->group(function () {
        Route::get('/knowledge/pending', [PendingKnowledgeController::class, 'index'])
            ->name('knowledge.pending');
        Route::post('/knowledge/pending/{pending}/approve', [PendingKnowledgeController::class, 'approve'])
            ->name('knowledge.pending.approve');
        Route::delete('/knowledge/pending/{pending}/reject', [PendingKnowledgeController::class, 'reject'])
            ->name('knowledge.pending.reject');
        Route::delete('/knowledge/pending/clear', [PendingKnowledgeController::class, 'clear'])
            ->name('knowledge.pending.clear');
    });

    // ---- Manajemen Knowledge (Chatbot) — SETELAH pending ----
    Route::resource('knowledge', KnowledgeController::class);

    // ---- Activity Log ----
    Route::get('/activity', [ActivityLogController::class, 'index'])->name('activity.index');
    Route::get('/activity/{activity}', [ActivityLogController::class, 'show'])->name('activity.show');
    Route::delete('/activity/clear', [ActivityLogController::class, 'clear'])->name('activity.clear');
    Route::delete('/activity/{activity}', [ActivityLogController::class, 'destroy'])->name('activity.destroy');

    // ---- Manajemen User (Admin Only) ----
    Route::middleware(['role:admin'])->group(function () {
        Route::resource('users', UserController::class);
    });

});

// ============================================================
// CHATBOT PUBLIK
// ============================================================
Route::get('/chat', [ChatController::class, 'index'])->name('chat.index');
Route::post('/chat/send', [ChatController::class, 'send'])
    ->middleware('throttle:20,1') // max 20 pesan/menit per IP
    ->name('chat.send');

// ============================================================
// PORTAL PUBLIK
// ============================================================
Route::get('/', [PortalController::class, 'index'])->name('portal');
Route::get('/portal/legacy', [PortalController::class, 'legacy'])->name('portal.legacy');
Route::get('/app/click/{id}', [PortalController::class, 'click'])->name('app.click');


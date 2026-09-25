<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PortalController;
use App\Http\Controllers\AppController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\KnowledgeController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\AssetTypeController;
use App\Http\Controllers\PendingKnowledgeController;
use App\Http\Controllers\UserController;

// ============ SIAM CONTROLLERS ============
use App\Http\Controllers\AssetController;
use App\Http\Controllers\AssetAssignmentController;
use App\Http\Controllers\AssetLoanController;
use App\Http\Controllers\AssetMaintenanceController;
use App\Http\Controllers\AssetCategoryController;
use App\Http\Controllers\VendorController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\ConsumableController;
use App\Http\Controllers\ConsumableTransactionController;
use App\Http\Controllers\SiamDashboardController;

// ============================================================
// AUTH ADMIN
// ============================================================
Route::post('/admin/login', [LoginController::class, 'login'])->name('admin.login');
Route::post('/admin/logout', [LoginController::class, 'logout'])->name('admin.logout');

// Placeholder route login
Route::get('/login', function () {
    return redirect('/');
})->name('login');

// ============================================================
// ROUTES YANG WAJIB LOGIN
// ============================================================
Route::middleware(['auth'])->group(function () {

    // ---- Manajemen Aplikasi ----
    Route::get('/apps', [AppController::class, 'index'])->name('apps.index');
    Route::get('/apps/create', [AppController::class, 'create'])->name('apps.create');
    Route::post('/apps', [AppController::class, 'store'])->name('apps.store');
    Route::get('/apps/{app}/edit', [AppController::class, 'edit'])->name('apps.edit');
    Route::put('/apps/{app}', [AppController::class, 'update'])->name('apps.update');
    Route::delete('/apps/{app}', [AppController::class, 'destroy'])->name('apps.destroy');

    // ---- Dashboard SIT ----
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // ---- Pending Knowledge (admin & support) ----
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

    // ---- Manajemen Knowledge ----
    Route::resource('knowledge', KnowledgeController::class);

    // ---- Activity Log ----
    Route::get('/activity', [ActivityLogController::class, 'index'])->name('activity.index');
    Route::get('/activity/{activity}', [ActivityLogController::class, 'show'])->name('activity.show');
    Route::delete('/activity/clear', [ActivityLogController::class, 'clear'])->name('activity.clear');
    Route::delete('/activity/{activity}', [ActivityLogController::class, 'destroy'])->name('activity.destroy');

    // ---- Manajemen User (Admin Only) ----
    // ✅ Semua user login boleh lihat profil (termasuk dirinya sendiri)
    Route::middleware(['auth'])->group(function () {
        Route::get('/users/{user}', [UserController::class, 'show'])->name('users.show');
    });

    // ✅ Hanya admin yang boleh kelola user
    Route::middleware(['role:admin'])->group(function () {
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
        Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
        Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
    });

    // ============================================================
    // 🆕 SIAM — HANYA ADMIN & SUPPORT
    // ============================================================
    Route::prefix('siam')
        ->name('siam.')
        ->middleware(['role:admin,support'])
        ->group(function () {

            //dashboard SIAM
            Route::get('/dashboard', [SiamDashboardController::class, 'index'])->name('dashboard');

            // ---- Master Data ----
            Route::resource('categories', AssetCategoryController::class);
            Route::resource('vendors', VendorController::class);
            Route::resource('departments', DepartmentController::class);
            Route::resource('locations', LocationController::class);

            // ---- Aset ----
            Route::resource('assets', AssetController::class);

            // ---- Assignment ----
            Route::resource('assignments', AssetAssignmentController::class)
                ->except(['edit', 'update']);
            Route::post('assignments/{assignment}/return', [AssetAssignmentController::class, 'returnAsset'])
                ->name('assignments.return');

            // ---- Loan ----
            Route::resource('loans', AssetLoanController::class)
                ->except(['edit', 'update']);
            Route::post('loans/{loan}/return', [AssetLoanController::class, 'returnAsset'])
                ->name('loans.return');

            // ---- Maintenance ----
            Route::resource('maintenances', AssetMaintenanceController::class);
            Route::post('maintenances/{maintenance}/complete', [AssetMaintenanceController::class, 'complete'])
                ->name('maintenances.complete');

            // ---- Consumable ----
            Route::resource('consumables', ConsumableController::class);

            // ---- Consumable Transactions ----
            Route::resource('consumable-transactions', ConsumableTransactionController::class)
                ->except(['edit', 'update'])
                ->parameters(['consumable-transactions' => 'transaction']);
            // ---- Asset Types ----
            Route::resource('asset-types', AssetTypeController::class);
        });

});

// ============================================================
// CHATBOT PUBLIK
// ============================================================
Route::get('/chat', [ChatController::class, 'index'])->name('chat.index');
Route::post('/chat/send', [ChatController::class, 'send'])
    ->middleware('throttle:20,1')
    ->name('chat.send');

// ============================================================
// PORTAL PUBLIK
// ============================================================
Route::get('/', [PortalController::class, 'index'])->name('portal');
Route::get('/portal/legacy', [PortalController::class, 'legacy'])->name('portal.legacy');
Route::get('/app/click/{id}', [PortalController::class, 'click'])->name('app.click');

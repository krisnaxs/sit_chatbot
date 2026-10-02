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
use App\Http\Controllers\UserAssetController;
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
use App\Http\Controllers\AssetRequestController;
use App\Http\Controllers\MyAssetController;
use App\Http\Controllers\QuickRequestController;
use App\Http\Controllers\AssetPublicController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\BackupController;
use App\Http\Controllers\AgentRegistryController;

/*
|--------------------------------------------------------------------------
| PUBLIC ROUTES — Guest & Auth
|--------------------------------------------------------------------------
| Portal bisa diakses tanpa login. Ini mencegah redirect loop.
*/
Route::get('/', [PortalController::class, 'index'])->name('portal');
Route::get('/portal/legacy', [PortalController::class, 'legacy'])->name('portal.legacy');
Route::get('/app/click/{id}', [PortalController::class, 'click'])->name('app.click');

Route::post('/admin/login', [LoginController::class, 'login'])->name('admin.login');
Route::post('/admin/logout', [LoginController::class, 'logout'])->name('admin.logout');

/**
 * /login — redirect cerdas biar tidak loop.
 */
Route::get('/login', function () {
    return auth()->check()
        ? redirect()->route('apps.index')
        : redirect()->route('portal');
})->name('login');

/*
|--------------------------------------------------------------------------
| AUTHENTICATED ROUTES
|--------------------------------------------------------------------------
*/

/*
|────────────────────────────────────────────────────────────
|  CHATBOT — PUBLIC (guest boleh akses)
|────────────────────────────────────────────────────────────
*/
Route::get('/chat', [ChatController::class, 'index'])->name('chat.index');
Route::post('/chat/send', [ChatController::class, 'send'])
    ->middleware('throttle:30,1')   // rate limit guest
    ->name('chat.send');

Route::get('/request/quick', [QuickRequestController::class, 'index'])->name('requests.quick');
Route::post('/request/quick', [QuickRequestController::class, 'store'])->name('requests.quick.store');
Route::get('/request/quick/receipt/{assetRequest}', [QuickRequestController::class, 'receipt'])->name('requests.quick.receipt');
Route::get('/a/{serial}', [AssetPublicController::class, 'show'])
    ->where('serial', '.*')
    ->middleware('throttle:60,1')
    ->name('assets.public');


Route::middleware(['auth'])->group(function () {
    Route::get('/apps', [AppController::class, 'index'])->name('apps.index');
    Route::get('/apps/create', [AppController::class, 'create'])->name('apps.create');
    Route::post('/apps', [AppController::class, 'store'])->name('apps.store');
    Route::get('/apps/{app}/edit', [AppController::class, 'edit'])->name('apps.edit');
    Route::put('/apps/{app}', [AppController::class, 'update'])->name('apps.update');
    Route::delete('/apps/{app}', [AppController::class, 'destroy'])->name('apps.destroy');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/ai/ask', [\App\Http\Controllers\AiAssetController::class, 'ask'])->name('ai.ask');
    Route::middleware(['role:admin,support'])->group(function () {
        Route::get('/knowledge/pending', [PendingKnowledgeController::class, 'index'])->name('knowledge.pending');
        Route::post('/knowledge/pending/{pending}/approve', [PendingKnowledgeController::class, 'approve'])->name('knowledge.pending.approve');
        Route::delete('/knowledge/pending/{pending}/reject', [PendingKnowledgeController::class, 'reject'])->name('knowledge.pending.reject');
        Route::delete('/knowledge/pending/clear', [PendingKnowledgeController::class, 'clear'])->name('knowledge.pending.clear');
    });
    Route::delete(
        '/knowledge/attachments/{attachment}',
        [KnowledgeController::class, 'destroyAttachment']
    )->name('knowledge.attachments.destroy');
    Route::resource('knowledge', KnowledgeController::class);
    Route::middleware(['role:admin'])->group(function () {

        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        Route::get('/users/import', [UserController::class, 'importForm'])->name('users.import.form');
        Route::get('/users/import/template', [UserController::class, 'downloadTemplate'])->name('users.import.template');
        Route::post('/users/import', [UserController::class, 'import'])->name('users.import');
        Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
        Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
        Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');


        Route::put('users/{user}/reset-password', [UserController::class, 'resetPassword'])
            ->name('users.reset-password');
    });
    Route::middleware(['role:admin,support'])
        ->prefix('siam/settings')
        ->name('settings.')
        ->group(function () {
            Route::get('/', [SettingController::class, 'index'])->name('index');
            Route::get('whatsapp', [SettingController::class, 'whatsapp'])->name('whatsapp');
            Route::put('whatsapp', [SettingController::class, 'updateWhatsapp'])->name('whatsapp.update');
            Route::post('whatsapp/test-connection', [SettingController::class, 'testWhatsapp'])->name('whatsapp.test');
            Route::post('whatsapp/test-send', [SettingController::class, 'testSend'])->name('whatsapp.test-send');
            Route::get('email', [SettingController::class, 'email'])->name('email');
            Route::put('email', [SettingController::class, 'updateEmail'])->name('email.update');
            Route::post('email/test', [SettingController::class, 'testEmail'])->name('email.test');
        });
    Route::middleware(['role:admin,support'])->group(function () {


        Route::get('/activity', [ActivityLogController::class, 'index'])->name('activity.index');
        Route::get('/activity/{activity}', [ActivityLogController::class, 'show'])->name('activity.show');
        Route::delete('/activity/clear', [ActivityLogController::class, 'clear'])->name('activity.clear');
        Route::delete('/activity/{activity}', [ActivityLogController::class, 'destroy'])->name('activity.destroy');
        Route::get('/admin/qr-code', function () {
            return view('qrcode.qr-code');
        })->name('admin.qr-code');
        Route::get('/admin/qr-generator', function () {
            return view('qrcode.qr-generator');
        })->name('admin.qr-generator');
    });
    Route::get('/users/{user}', [UserController::class, 'show'])->name('users.show');
    Route::put('users/{user}/password', [UserController::class, 'updatePassword'])
        ->name('users.update-password');

    /*
    |────────────────────────────────────────────────────────────
    | SIAM — Asset Management
    |────────────────────────────────────────────────────────────
    */
    Route::prefix('siam')
        ->name('siam.')
        ->middleware(['role:admin,support'])
        ->group(function () {
            Route::get('/dashboard', [SiamDashboardController::class, 'index'])->name('dashboard');
            Route::resource('categories', AssetCategoryController::class);
            Route::resource('vendors', VendorController::class);
            Route::resource('departments', DepartmentController::class);
            Route::resource('locations', LocationController::class);
            Route::prefix('backup')->name('backup.')->group(function () {
                Route::get('/', [BackupController::class, 'index'])->name('index');
                Route::post('/create', [BackupController::class, 'create'])->name('create');
                Route::get('/download/{filename}', [BackupController::class, 'download'])->name('download');
                Route::delete('/{filename}', [BackupController::class, 'destroy'])->name('destroy');
                Route::post('/restore', [BackupController::class, 'restore'])->name('restore');
            });
            Route::get('assets/bulk-create', [AssetController::class, 'bulkCreate'])->name('assets.bulk-create');
            Route::post('assets/bulk-store', [AssetController::class, 'bulkStore'])->name('assets.bulk-store');
            Route::post('assets/bulk-preview', [AssetController::class, 'bulkPreview'])->name('assets.bulk-preview');
            Route::resource('asset-types', AssetTypeController::class);
            Route::get('assets/export/excel', [AssetController::class, 'exportExcel'])->name('assets.export.excel');
            Route::get('assets/export/pdf', [AssetController::class, 'exportPdf'])->name('assets.export.pdf');
            Route::get('assets/{asset}/qr', [AssetController::class, 'qrCode'])
                ->name('assets.qr');
            Route::get('assets/qr-batch', [AssetController::class, 'qrBatch'])
                ->name('assets.qr.batch');
            Route::get('assets/qr-preview/{asset}', [AssetController::class, 'qrPreview'])
                ->name('assets.qr.preview');

            Route::get('assets/monitoring', [AssetController::class, 'monitoring'])
                ->name('assets.monitoring');
            Route::resource('assets', AssetController::class);
            Route::resource('assignments', AssetAssignmentController::class)->except(['edit', 'update']);
            Route::post('assignments/{assignment}/return', [AssetAssignmentController::class, 'returnAsset'])->name('assignments.return');
            Route::resource('loans', AssetLoanController::class)->except(['edit', 'update']);
            Route::post('loans/{loan}/return', [AssetLoanController::class, 'returnAsset'])->name('loans.return');
            Route::resource('maintenances', AssetMaintenanceController::class);
            Route::post('maintenances/{maintenance}/complete', [AssetMaintenanceController::class, 'complete'])->name('maintenances.complete');
            Route::get('consumables/export/excel', [ConsumableController::class, 'exportExcel'])->name('consumables.export.excel');
            Route::get('consumables/export/pdf', [ConsumableController::class, 'exportPdf'])->name('consumables.export.pdf');
            Route::resource('consumables', ConsumableController::class);
            Route::get('consumable-transactions/export/excel', [ConsumableTransactionController::class, 'exportExcel'])->name('consumable-transactions.export.excel');
            Route::get('consumable-transactions/export/pdf', [ConsumableTransactionController::class, 'exportPdf'])->name('consumable-transactions.export.pdf');
            Route::resource('consumable-transactions', ConsumableTransactionController::class)
                ->except(['edit', 'update'])
                ->parameters(['consumable-transactions' => 'transaction']);
            Route::get('user-assets', [UserAssetController::class, 'index'])->name('user-assets.index');
            Route::get('user-assets/export/excel', [UserAssetController::class, 'exportExcel'])->name('user-assets.export.excel');
            Route::get('user-assets/export/pdf', [UserAssetController::class, 'exportPdf'])->name('user-assets.export.pdf');

            // === AGENT REGISTRY ===
            Route::prefix('agent-registry')
                ->name('agent-registry.')
                ->middleware(['role:admin,support'])
                ->group(function () {
                Route::get('/', [AgentRegistryController::class, 'index'])->name('index');

                // Approve & Reject
                Route::post('/{pending}/approve', [AgentRegistryController::class, 'approve'])->name('approve');
                Route::post('/{pending}/reject', [AgentRegistryController::class, 'reject'])->name('reject');

                // Hapus permanen (yang sudah di-reject)
                Route::delete('/{pending}/force-delete', [AgentRegistryController::class, 'forceDelete'])->name('force-delete');

                // Bersihkan
                Route::delete('/clear', [AgentRegistryController::class, 'clear'])->name('clear');
                Route::delete('/clear-rejected', [AgentRegistryController::class, 'clearRejected'])->name('clear-rejected');

                // Token actions
                Route::post('/tokens/{token}/revoke', [AgentRegistryController::class, 'revokeToken'])->name('tokens.revoke');
                Route::post('/tokens/{token}/activate', [AgentRegistryController::class, 'activateToken'])->name('tokens.activate');
                Route::post('/tokens/{token}/regenerate', [AgentRegistryController::class, 'regenerateToken'])->name('tokens.regenerate');
            });


        });


    /*
    |────────────────────────────────────────────────────────────
    | PENGAJUAN — User Side (semua role)
    |────────────────────────────────────────────────────────────
    | Urutan PENTING: /my & /create dulu, baru /{assetRequest}
    */
    Route::prefix('requests')->name('requests.')->group(function () {
        Route::get('/my', [AssetRequestController::class, 'myRequests'])->name('my');
        Route::get('/create', [AssetRequestController::class, 'create'])->name('create');
        Route::post('/', [AssetRequestController::class, 'store'])->name('store');
        Route::post('/{assetRequest}/cancel', [AssetRequestController::class, 'cancel'])->name('cancel');
        Route::get('/{assetRequest}', [AssetRequestController::class, 'show'])->name('show');

    });
    Route::get('/my-assets', [MyAssetController::class, 'index'])->name('my-assets.index');
    /*
    |────────────────────────────────────────────────────────────
    | PENGAJUAN — Admin/Support (Approval)
    |────────────────────────────────────────────────────────────
    */
    Route::prefix('admin/requests')
        ->name('admin.requests.')
        ->middleware(['role:admin,support'])
        ->group(function () {
            Route::get('/', [AssetRequestController::class, 'index'])->name('index');
            Route::post('/{assetRequest}/approve', [AssetRequestController::class, 'approve'])->name('approve');
            Route::post('/{assetRequest}/reject', [AssetRequestController::class, 'reject'])->name('reject');
        });
});

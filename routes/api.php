<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AgentController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Default (dari Laravel)
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

/*
|--------------------------------------------------------------------------
| Agent Routes
|--------------------------------------------------------------------------
*/
Route::prefix('agent')->name('agent.')->group(function () {

    // Auto-register — 5x per jam per IP
    // Alasan: agent hanya register SEKALI saat install pertama.
    // Setelah dapat token, tidak akan register lagi (kecuali token dihapus).
    Route::post('/register', [AgentController::class, 'register'])
        ->middleware('throttle:5,60')
        ->name('register');

    // Heartbeat — 30x per menit per token
    // Agent kirim heartbeat tiap 5 menit. 30x/menit = buffer besar untuk retry.
    Route::post('/heartbeat', [AgentController::class, 'heartbeat'])
        ->middleware('throttle:30,1')
        ->name('heartbeat');

    // Info agent (debug) — butuh token
    Route::get('/me', [AgentController::class, 'me'])
        ->name('me');
});

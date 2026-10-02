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

    // Auto-register — tidak butuh token (pakai serial BIOS)
    // Rate limit: 5x per jam per IP
    Route::post('/register', [AgentController::class, 'register'])
        ->middleware('throttle:5,60')
        ->name('register');

    // Heartbeat — butuh token (Bearer)
    // Rate limit: 60x per menit per token
    Route::post('/heartbeat', [AgentController::class, 'heartbeat'])
        ->middleware('throttle:60,1')
        ->name('heartbeat');

    // Info agent (debug) — butuh token
    Route::get('/me', [AgentController::class, 'me'])
        ->name('me');
});

<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AgentController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

/*
|--------------------------------------------------------------------------
| Agent Routes
|--------------------------------------------------------------------------
*/
Route::prefix('agent')->name('agent.')->group(function () {
    Route::post('/register', [AgentController::class, 'register'])
        ->middleware('throttle:5,60')
        ->name('register');
    Route::post('/heartbeat', [AgentController::class, 'heartbeat'])
        ->middleware('throttle:30,1')
        ->name('heartbeat');
    Route::get('/me', [AgentController::class, 'me'])
        ->name('me');
});

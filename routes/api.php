<?php

use App\Http\Controllers\Api\AuthController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes — PinjamIn Mobile
|--------------------------------------------------------------------------
|
| Semua route di sini di-prefix dengan /api secara otomatis oleh Laravel.
| Gunakan Sanctum token untuk autentikasi pada route yang dilindungi.
|
*/

// ─── Auth Routes (tidak butuh login) ─────────────────────────────────────────
Route::prefix('auth')->group(function () {
    Route::post('login',           [AuthController::class, 'login']);
    Route::post('forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('reset-password',  [AuthController::class, 'resetPassword']);
});

// ─── Protected Routes (butuh Sanctum token) ───────────────────────────────────
Route::middleware('auth:sanctum')->group(function () {
    Route::prefix('auth')->group(function () {
        Route::get('me',     [AuthController::class, 'me']);
        Route::post('logout', [AuthController::class, 'logout']);
    });

    // Tambahkan route lain di sini sesuai kebutuhan
    // Contoh:
    // Route::apiResource('peminjaman', PeminjamanController::class);
});

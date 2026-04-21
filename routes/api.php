<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ItemController;
use App\Http\Controllers\Api\LoanController;
use App\Http\Controllers\Api\ReturnController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes — PinjamIn Mobile
|--------------------------------------------------------------------------
*/

// ─── Public Auth Routes ──────────────────────────────────────────────────────
Route::prefix('auth')->group(function () {
    Route::post('login',           [AuthController::class, 'login']);
    Route::post('forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('reset-password',  [AuthController::class, 'resetPassword']);
    Route::post('activate-account', [AuthController::class, 'activateAccount']);
});

// ─── Protected Routes (Sanctum) ──────────────────────────────────────────────
Route::middleware('auth:sanctum')->group(function () {
    
    // Auth Profile & Logout
    Route::prefix('auth')->group(function () {
        Route::get('me',      [AuthController::class, 'me']);
        Route::post('logout', [AuthController::class, 'logout']);
    });

    // Items
    Route::get('items', [ItemController::class, 'index']);

    // Loans
    Route::get('loans',  [LoanController::class, 'index']);
    Route::post('loans', [LoanController::class, 'store']);

    // Returns
    Route::get('returns', [ReturnController::class, 'index']);
    Route::get('returns/active-loans', [ReturnController::class, 'getActiveLoans']);
    Route::post('returns', [ReturnController::class, 'store']);

    // Fines
    Route::get('fines', [\App\Http\Controllers\Api\FineController::class, 'index']);
});

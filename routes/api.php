<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ItemController;
use App\Http\Controllers\Api\LoanController;
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
});

// ─── Protected Routes (Sanctum) ──────────────────────────────────────────────
Route::middleware('auth:sanctum')->group(function () {
    
    // Auth Profile & Logout (pake prefix auth supaya match dengan React)
    Route::prefix('auth')->group(function () {
        Route::get('me',      [AuthController::class, 'me']);
        Route::post('logout', [AuthController::class, 'logout']);
    });

    // Items
    Route::get('items', [ItemController::class, 'index']);

    // Loans
    Route::get('loans',  [LoanController::class, 'index']);
    Route::post('loans', [LoanController::class, 'store']);

});

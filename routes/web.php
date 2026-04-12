<?php

use App\Http\Controllers\Admin\CategoryManagementController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ItemManagementController;
use App\Http\Controllers\Admin\ManagementPlaceholderController;
use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (auth()->user()->hasRole('admin')) {
        return redirect()->route('admin.dashboard');
    }

    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth', 'verified', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    Route::get('/manajemen/kategori', [CategoryManagementController::class, 'index'])->name('categories.index');
    Route::get('/manajemen/kategori/create', [CategoryManagementController::class, 'create'])->name('categories.create');
    Route::post('/manajemen/kategori', [CategoryManagementController::class, 'store'])->name('categories.store');
    Route::get('/manajemen/kategori/{category}/edit', [CategoryManagementController::class, 'edit'])->name('categories.edit');
    Route::put('/manajemen/kategori/{category}', [CategoryManagementController::class, 'update'])->name('categories.update');
    Route::delete('/manajemen/kategori/{category}', [CategoryManagementController::class, 'destroy'])->name('categories.destroy');

    Route::get('/manajemen/barang', [ItemManagementController::class, 'index'])->name('items.index');
    Route::get('/manajemen/barang/create', [ItemManagementController::class, 'create'])->name('items.create');
    Route::post('/manajemen/barang', [ItemManagementController::class, 'store'])->name('items.store');
    Route::get('/manajemen/barang/{item}', [ItemManagementController::class, 'show'])->name('items.show');
    Route::get('/manajemen/barang/{item}/edit', [ItemManagementController::class, 'edit'])->name('items.edit');
    Route::put('/manajemen/barang/{item}', [ItemManagementController::class, 'update'])->name('items.update');
    Route::delete('/manajemen/barang/{item}', [ItemManagementController::class, 'destroy'])->name('items.destroy');

    Route::get('/manajemen/peminjaman', [ManagementPlaceholderController::class, 'loan'])->name('loans.index');
    Route::get('/manajemen/pengembalian', [ManagementPlaceholderController::class, 'loanReturn'])->name('returns.index');
    Route::get('/manajemen/log-aktifitas', [ManagementPlaceholderController::class, 'activityLog'])->name('activity-log.index');

    Route::get('/manajemen/users', [UserManagementController::class, 'index'])->name('users.index');
    Route::get('/manajemen/users/create', [UserManagementController::class, 'create'])->name('users.create');
    Route::post('/manajemen/users', [UserManagementController::class, 'store'])->name('users.store');
    Route::get('/manajemen/users/{user}/edit', [UserManagementController::class, 'edit'])->name('users.edit');
    Route::put('/manajemen/users/{user}', [UserManagementController::class, 'update'])->name('users.update');
    Route::delete('/manajemen/users/{user}', [UserManagementController::class, 'destroy'])->name('users.destroy');
});

require __DIR__.'/auth.php';

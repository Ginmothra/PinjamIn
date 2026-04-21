<?php

use App\Http\Controllers\Admin\CategoryManagementController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ItemManagementController;
use App\Http\Controllers\Admin\LoanManagementController;
use App\Http\Controllers\Admin\LoanReturnManagementController;
use App\Http\Controllers\Admin\ManagementPlaceholderController;
use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\Worker\DashboardController as WorkerDashboardController;
use App\Http\Controllers\Worker\LoansController as WorkerLoansController;
use App\Http\Controllers\Worker\ReturnsController as WorkerReturnsController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Auth\AccountActivationController;
use App\Http\Controllers\Worker\ReportController;
use Illuminate\Support\Facades\Route;

Route::get('/activate-account', [AccountActivationController::class, 'show'])->name('activate-account');
Route::post('/activate-account', [AccountActivationController::class, 'store'])->name('activate-account.store');

Route::get('/', function () {
    if (auth()->user()->hasRole('admin')) {
        return redirect()->route('admin.dashboard');
    }

    if (auth()->user()->hasRole('worker')) {
        return redirect()->route('worker.dashboard');
    }

    if (auth()->user()->hasRole('technician')) {
        return redirect()->route('technician.dashboard');
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
    Route::get('/manajemen/barang/export', [ItemManagementController::class, 'export'])->name('items.export');
    Route::get('/manajemen/barang/template', [ItemManagementController::class, 'template'])->name('items.template');
    Route::post('/manajemen/barang/import', [ItemManagementController::class, 'import'])->name('items.import');
    Route::get('/manajemen/barang/create', [ItemManagementController::class, 'create'])->name('items.create');
    Route::post('/manajemen/barang', [ItemManagementController::class, 'store'])->name('items.store');
    Route::get('/manajemen/barang/{item}', [ItemManagementController::class, 'show'])->name('items.show');
    Route::get('/manajemen/barang/{item}/edit', [ItemManagementController::class, 'edit'])->name('items.edit');
    Route::put('/manajemen/barang/{item}', [ItemManagementController::class, 'update'])->name('items.update');
    Route::delete('/manajemen/barang/{item}', [ItemManagementController::class, 'destroy'])->name('items.destroy');

    Route::get('/manajemen/peminjaman', [LoanManagementController::class, 'index'])->name('loans.index');
    Route::get('/manajemen/peminjaman/create', [LoanManagementController::class, 'create'])->name('loans.create');
    Route::post('/manajemen/peminjaman', [LoanManagementController::class, 'store'])->name('loans.store');
    Route::get('/manajemen/peminjaman/{loan}', [LoanManagementController::class, 'show'])->name('loans.show');
    Route::get('/manajemen/peminjaman/{loan}/edit', [LoanManagementController::class, 'edit'])->name('loans.edit');
    Route::put('/manajemen/peminjaman/{loan}', [LoanManagementController::class, 'update'])->name('loans.update');
    Route::delete('/manajemen/peminjaman/{loan}', [LoanManagementController::class, 'destroy'])->name('loans.destroy');
    Route::get('/manajemen/pengembalian', [LoanReturnManagementController::class, 'index'])->name('returns.index');
    Route::get('/manajemen/pengembalian/create', [LoanReturnManagementController::class, 'create'])->name('returns.create');
    Route::post('/manajemen/pengembalian', [LoanReturnManagementController::class, 'store'])->name('returns.store');
    Route::get('/manajemen/pengembalian/{loanReturn}', [LoanReturnManagementController::class, 'show'])->name('returns.show');
    Route::get('/manajemen/pengembalian/{loanReturn}/edit', [LoanReturnManagementController::class, 'edit'])->name('returns.edit');
    Route::put('/manajemen/pengembalian/{loanReturn}', [LoanReturnManagementController::class, 'update'])->name('returns.update');
    Route::delete('/manajemen/pengembalian/{loanReturn}', [LoanReturnManagementController::class, 'destroy'])->name('returns.destroy');
    Route::get('/manajemen/log-aktifitas', [ManagementPlaceholderController::class, 'activityLog'])->name('activity-log.index');

    Route::get('/manajemen/users', [UserManagementController::class, 'index'])->name('users.index');
    Route::get('/manajemen/users/export', [UserManagementController::class, 'export'])->name('users.export');
    Route::get('/manajemen/users/template', [UserManagementController::class, 'template'])->name('users.template');
    Route::post('/manajemen/users/import', [UserManagementController::class, 'import'])->name('users.import');
    Route::get('/manajemen/users/create', [UserManagementController::class, 'create'])->name('users.create');
    Route::post('/manajemen/users', [UserManagementController::class, 'store'])->name('users.store');
    Route::get('/manajemen/users/{user}/edit', [UserManagementController::class, 'edit'])->name('users.edit');
    Route::put('/manajemen/users/{user}', [UserManagementController::class, 'update'])->name('users.update');
    Route::post('/manajemen/users/{user}/resend-activation', [UserManagementController::class, 'resendActivation'])->name('users.resend-activation');
    Route::delete('/manajemen/users/{user}', [UserManagementController::class, 'destroy'])->name('users.destroy');
});

Route::middleware(['auth', 'verified', 'role:worker'])->prefix('worker')->name('worker.')->group(function () {
    Route::get('/dashboard', [WorkerDashboardController::class, 'index'])->name('dashboard');

    Route::get('/peminjaman', [WorkerLoansController::class, 'index'])->name('loans.index');
    Route::post('/peminjaman/{loan}/approve', [WorkerLoansController::class, 'approve'])->name('loans.approve');
    Route::get('/peminjaman/{loan}/reject', [WorkerLoansController::class, 'rejectForm'])->name('loans.reject-form');
    Route::post('/peminjaman/{loan}/reject', [WorkerLoansController::class, 'reject'])->name('loans.reject');

    Route::get('/pengembalian', [WorkerReturnsController::class, 'index'])->name('returns.index');
    Route::get('/pengembalian/{loanReturn}', [WorkerReturnsController::class, 'show'])->name('returns.show');
    Route::post('/pengembalian/{loanReturn}/accept', [WorkerReturnsController::class, 'accept'])->name('returns.accept');
    Route::post('/pengembalian/{loanReturn}/reject', [WorkerReturnsController::class, 'reject'])->name('returns.reject');
    Route::get('/pengembalian/{loanReturn}/damaged', [WorkerReturnsController::class, 'damagedForm'])->name('returns.damaged');
    Route::post('/pengembalian/{loanReturn}/damaged', [WorkerReturnsController::class, 'markDamaged'])->name('returns.mark-damaged');
    Route::post('/reports/generate', [ReportController::class, 'generate'])->name('reports.generate');
});

Route::middleware(['auth', 'verified', 'role:technician'])->prefix('technician')->name('technician.')->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\Technician\RepairController::class, 'index'])->name('dashboard');
    Route::get('/repairs/history', [\App\Http\Controllers\Technician\RepairController::class, 'history'])->name('repairs.history');
    Route::post('/repairs/{repair}/start', [\App\Http\Controllers\Technician\RepairController::class, 'startRepair'])->name('repairs.start');
    Route::post('/repairs/{repair}/update', [\App\Http\Controllers\Technician\RepairController::class, 'updateProgress'])->name('repairs.update');
    Route::post('/repairs/{repair}/complete', [\App\Http\Controllers\Technician\RepairController::class, 'completeRepair'])->name('repairs.complete');
});

require __DIR__.'/auth.php';

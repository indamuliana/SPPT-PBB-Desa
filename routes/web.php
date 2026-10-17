<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PublicController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VillageProfileController;
use Illuminate\Support\Facades\Route;

// Public Landing Page
Route::get('/', [PublicController::class, 'home'])->name('home');

// Authentication Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// Public Receipt Verification via QR Code
Route::get('/verifikasi/{token}', [\App\Http\Controllers\PublicVerificationController::class, 'verify'])
    ->name('verification.verify')
    ->middleware('throttle:60,1');

// Protected Application Routes
Route::middleware('auth')->group(function () {
    // Dashboard accessible by all authenticated roles
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Loket Kasir POS & Transaksi Pembayaran
    Route::get('/pos', [\App\Http\Controllers\PaymentController::class, 'pos'])->name('payments.pos');
    Route::post('/payments', [\App\Http\Controllers\PaymentController::class, 'store'])->name('payments.store');
    Route::get('/payments/history', [\App\Http\Controllers\PaymentController::class, 'history'])->name('payments.history');
    Route::get('/payments/{payment}/receipt', [\App\Http\Controllers\PaymentController::class, 'receipt'])->name('payments.receipt');
    Route::post('/payments/{payment}/cancel', [\App\Http\Controllers\PaymentController::class, 'requestCancellation'])->name('payments.cancel.request');

    // Admin Pembatalan Transaksi Approval
    Route::middleware('role:admin')->group(function () {
        Route::post('/payments/{payment}/cancel/approve', [\App\Http\Controllers\PaymentController::class, 'approveCancellation'])->name('payments.cancel.approve');
        Route::post('/payments/{payment}/cancel/reject', [\App\Http\Controllers\PaymentController::class, 'rejectCancellation'])->name('payments.cancel.reject');
    });

    // Admin Only Settings & User Management
    Route::middleware('role:admin')->group(function () {
        Route::get('/settings/village', [VillageProfileController::class, 'edit'])->name('settings.village');
        Route::put('/settings/village', [VillageProfileController::class, 'update'])->name('settings.village.update');

        Route::resource('users', UserController::class)->except(['show', 'destroy']);
        Route::post('/users/{user}/toggle', [UserController::class, 'toggleStatus'])->name('users.toggle');
    });

    // Master Data Pajak & SPPT (Semua petugas bisa melihat data)
    Route::get('/taxpayers', [\App\Http\Controllers\TaxpayerController::class, 'index'])->name('taxpayers.index');
    Route::get('/tax-objects', [\App\Http\Controllers\TaxObjectController::class, 'index'])->name('tax-objects.index');
    Route::get('/sppts', [\App\Http\Controllers\SpptController::class, 'index'])->name('sppts.index');

    // Admin Master Data & DHKP Ingestion
    Route::middleware('role:admin')->group(function () {
        Route::get('/taxpayers/create', [\App\Http\Controllers\TaxpayerController::class, 'create'])->name('taxpayers.create');
        Route::post('/taxpayers', [\App\Http\Controllers\TaxpayerController::class, 'store'])->name('taxpayers.store');
        Route::get('/taxpayers/{taxpayer}/edit', [\App\Http\Controllers\TaxpayerController::class, 'edit'])->name('taxpayers.edit');
        Route::put('/taxpayers/{taxpayer}', [\App\Http\Controllers\TaxpayerController::class, 'update'])->name('taxpayers.update');

        Route::get('/tax-objects/create', [\App\Http\Controllers\TaxObjectController::class, 'create'])->name('tax-objects.create');
        Route::post('/tax-objects', [\App\Http\Controllers\TaxObjectController::class, 'store'])->name('tax-objects.store');
        Route::get('/tax-objects/{taxObject}/edit', [\App\Http\Controllers\TaxObjectController::class, 'edit'])->name('tax-objects.edit');
        Route::put('/tax-objects/{taxObject}', [\App\Http\Controllers\TaxObjectController::class, 'update'])->name('tax-objects.update');

        Route::get('/sppts/create', [\App\Http\Controllers\SpptController::class, 'create'])->name('sppts.create');
        Route::post('/sppts', [\App\Http\Controllers\SpptController::class, 'store'])->name('sppts.store');
        Route::get('/sppts/import', [\App\Http\Controllers\SpptController::class, 'importView'])->name('sppts.import.view');
        Route::post('/sppts/import', [\App\Http\Controllers\SpptController::class, 'importProcess'])->name('sppts.import.process');
    });

    // Audit logs for Admin, Kades, and Bendahara
    Route::middleware('role:admin,kades,bendahara')->group(function () {
        Route::get('/audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');
    });
});

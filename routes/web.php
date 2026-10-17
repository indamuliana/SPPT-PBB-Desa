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

// Protected Application Routes
Route::middleware('auth')->group(function () {
    // Dashboard accessible by all authenticated roles
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Admin Only Settings & User Management
    Route::middleware('role:admin')->group(function () {
        Route::get('/settings/village', [VillageProfileController::class, 'edit'])->name('settings.village');
        Route::put('/settings/village', [VillageProfileController::class, 'update'])->name('settings.village.update');

        Route::resource('users', UserController::class)->except(['show', 'destroy']);
        Route::post('/users/{user}/toggle', [UserController::class, 'toggleStatus'])->name('users.toggle');
    });

    // Audit logs for Admin, Kades, and Bendahara
    Route::middleware('role:admin,kades,bendahara')->group(function () {
        Route::get('/audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');
    });
});

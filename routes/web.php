<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Api\WhatsAppLicenseController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/licenses', [DashboardController::class, 'licenses'])->name('licenses');
    Route::get('/logs', [DashboardController::class, 'logs'])->name('logs');
    Route::get('/analytics', [DashboardController::class, 'analytics'])->name('analytics');
    
    // Rotas web para gerenciamento de licenças (para o painel administrativo)
    Route::prefix('admin/licenses')->name('admin.licenses.')->group(function () {
        Route::post('/generate', [WhatsAppLicenseController::class, 'generateKey'])->name('generate');
        Route::post('/{license}/renew', [WhatsAppLicenseController::class, 'renewLicense'])->name('renew');
        Route::delete('/{license}/deactivate', [WhatsAppLicenseController::class, 'deactivateLicense'])->name('deactivate');
    });
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';

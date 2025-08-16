<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\WhatsAppLicenseController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Rotas públicas (para verificação de licenças)
Route::post('/whatsapp/verify-key', [WhatsAppLicenseController::class, 'verifyKey']);

// Rotas protegidas (para administração) - aceita tanto web quanto sanctum
Route::middleware(['auth:web,sanctum'])->group(function () {
    
    // Gerenciamento de licenças
    Route::prefix('whatsapp/licenses')->group(function () {
        Route::get('/', [WhatsAppLicenseController::class, 'listLicenses']);
        Route::post('/generate', [WhatsAppLicenseController::class, 'generateKey']);
        Route::post('/{license}/renew', [WhatsAppLicenseController::class, 'renewLicense']);
        Route::delete('/{license}/deactivate', [WhatsAppLicenseController::class, 'deactivateLicense']);
        Route::get('/stats', [WhatsAppLicenseController::class, 'getStats']);
    });
});

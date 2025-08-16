<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Api\WhatsAppLicenseController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
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
    
    // Rota de teste para verificar conectividade do banco
    Route::get('/admin/test-db', function () {
        try {
            $dbConnection = DB::connection()->getPdo();
            $tables = DB::select("SHOW TABLES");
            
            return response()->json([
                'success' => true,
                'message' => 'Conexão com banco OK',
                'tables_count' => count($tables),
                'tables' => collect($tables)->pluck('Tables_in_' . env('DB_DATABASE', 'database'))->toArray()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro de conexão com banco',
                'error' => $e->getMessage()
            ], 500);
        }
    })->name('admin.test.db');
    
    // Rota de teste para validar criação de licença
    Route::post('/admin/test-license', function () {
        try {
            // Teste de validação
            $testData = [
                'client_name' => 'Teste Cliente',
                'client_email' => 'teste@exemplo.com',
                'client_phone' => '11999999999',
                'license_value' => 50.00,
                'plan_type' => 'basic',
                'duration_days' => 30
            ];
            
            // Verificar se as tabelas existem
            $licenseTableExists = DB::select("SHOW TABLES LIKE 'whats_app_licenses'");
            $logTableExists = DB::select("SHOW TABLES LIKE 'whats_app_license_logs'");
            
            return response()->json([
                'success' => true,
                'message' => 'Testes OK',
                'test_data' => $testData,
                'license_table_exists' => !empty($licenseTableExists),
                'log_table_exists' => !empty($logTableExists)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro no teste',
                'error' => $e->getMessage()
            ], 500);
        }
    })->name('admin.test.license');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';

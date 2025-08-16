<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\WhatsAppLicense;
use App\Models\WhatsAppLicenseLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class WhatsAppLicenseController extends Controller
{
    /**
     * Verifica uma chave de acesso
     * Pode ser usado de duas formas:
     * 1. Apenas access_key - verificação simples
     * 2. access_key + device_id - verificação completa com vinculação de dispositivo
     */
    public function verifyKey(Request $request)
    {
        $request->validate([
            'access_key' => 'required|string',
            'device_id' => 'nullable|string',
            'timestamp' => 'nullable|integer'
        ]);

        try {
            $accessKey = $request->access_key;
            $deviceId = $request->device_id;
            $timestamp = $request->timestamp;
            $isSimpleCheck = !$deviceId; // Se não tem device_id, é verificação simples

            // Busca a licença na base de dados
            $license = WhatsAppLicense::where('access_key', $accessKey)
                ->where('status', 'active')
                ->first();

            if (!$license) {
                return response()->json([
                    'success' => false,
                    'message' => 'Chave de acesso inválida ou inativa',
                    'valid' => false
                ], 401);
            }

            // Verifica se a licença expirou (apenas para licenças com prazo)
            if ($license->expires_at && $license->is_expired) {
                // Atualiza status para expirado
                $license->update(['status' => 'expired']);

                return response()->json([
                    'success' => false,
                    'message' => 'Licença expirada',
                    'valid' => false
                ], 401);
            }

            // Se for verificação simples (apenas access_key), retorna apenas se é válida
            if ($isSimpleCheck) {
                return response()->json([
                    'success' => true,
                    'message' => 'Chave de acesso válida',
                    'valid' => true,
                    'expires_at' => $license->expires_at,
                    'remaining_days' => $license->remaining_days,
                    'plan_type' => $license->plan_type,
                    'client_name' => $license->client_name
                ]);
            }

            // Verificação completa com device_id
            // Verifica se já está sendo usado em outro dispositivo
            if ($license->device_id && $license->device_id !== $deviceId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Esta licença já está ativa em outro dispositivo',
                    'valid' => true, // A licença é válida, mas está em uso
                    'device_conflict' => true
                ], 403);
            }

            // Atualiza informações de uso
            $license->update([
                'device_id' => $deviceId,
                'last_verification' => now(),
                'last_ip' => $request->ip(),
                'last_user_agent' => $request->header('User-Agent')
            ]);

            // Registra log de acesso
            WhatsAppLicenseLog::create([
                'license_id' => $license->id,
                'device_id' => $deviceId,
                'action' => 'verify',
                'ip_address' => $request->ip(),
                'user_agent' => $request->header('User-Agent'),
                'created_at' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Licença verificada com sucesso',
                'valid' => true,
                'expires_at' => $license->expires_at,
                'remaining_days' => $license->remaining_days,
                'plan_type' => $license->plan_type,
                'client_name' => $license->client_name,
                'device_linked' => true
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro interno do servidor',
                'valid' => false
            ], 500);
        }
    }

    /**
     * Gera uma nova chave de acesso
     */
    public function generateKey(Request $request)
    {
        $request->validate([
            'client_name' => 'required|string|max:255',
            'client_email' => 'nullable|email|max:255',
            'client_phone' => 'nullable|string|max:20',
            'license_value' => 'required|numeric|min:0',
            'plan_type' => 'required|in:basic,professional,enterprise',
            'duration_days' => 'required|integer|min:0|max:365'
        ]);

        try {
            $accessKey = $this->generateAccessKey();
            
            // Se duration_days for 0, é lifetime (expires_at = null)
            $expiresAt = $request->duration_days > 0 ? Carbon::now()->addDays($request->duration_days) : null;

            $license = WhatsAppLicense::create([
                'access_key' => $accessKey,
                'client_name' => $request->client_name,
                'client_email' => $request->client_email,
                'client_phone' => $request->client_phone,
                'license_value' => $request->license_value,
                'plan_type' => $request->plan_type,
                'status' => 'active',
                'expires_at' => $expiresAt,
            ]);

            // Registra log de criação
            WhatsAppLicenseLog::create([
                'license_id' => $license->id,
                'action' => 'created',
                'ip_address' => $request->ip(),
                'user_agent' => $request->header('User-Agent'),
                'created_at' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Licença gerada com sucesso',
                'access_key' => $accessKey,
                'expires_at' => $expiresAt,
                'plan_type' => $request->plan_type
            ]);

        } catch (\Exception $e) {
            // Log do erro para debugging
            \Log::error('Erro ao gerar licença: ' . $e->getMessage(), [
                'exception' => $e,
                'request_data' => $request->all()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Erro ao gerar licença',
                'debug' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Lista todas as licenças
     */
    public function listLicenses(Request $request)
    {
        $query = WhatsAppLicense::with('logs');

        // Filtros opcionais
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('plan_type')) {
            $query->where('plan_type', $request->plan_type);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('client_name', 'like', "%{$search}%")
                  ->orWhere('client_email', 'like', "%{$search}%")
                  ->orWhere('access_key', 'like', "%{$search}%");
            });
        }

        $licenses = $query->orderBy('created_at', 'desc')
                         ->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $licenses
        ]);
    }

    /**
     * Renovar uma licença
     */
    public function renewLicense(Request $request, $licenseId)
    {
        $request->validate([
            'duration_days' => 'required|integer|min:1|max:365'
        ]);

        try {
            $license = WhatsAppLicense::find($licenseId);

            if (!$license) {
                return response()->json([
                    'success' => false,
                    'message' => 'Licença não encontrada'
                ], 404);
            }

            $newExpiresAt = Carbon::now()->addDays($request->duration_days);

            $license->update([
                'expires_at' => $newExpiresAt,
                'status' => 'active'
            ]);

            // Registra log de renovação
            WhatsAppLicenseLog::create([
                'license_id' => $licenseId,
                'action' => 'renewed',
                'ip_address' => $request->ip(),
                'user_agent' => $request->header('User-Agent'),
                'created_at' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Licença renovada com sucesso',
                'expires_at' => $newExpiresAt
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao renovar licença'
            ], 500);
        }
    }

    /**
     * Desativar uma licença
     */
    public function deactivateLicense($license)
    {
        try {
            $licenseModel = WhatsAppLicense::find($license);

            if (!$licenseModel) {
                return redirect()->back()->with('error', 'Licença não encontrada');
            }

            $licenseModel->update([
                'status' => 'inactive',
                'device_id' => null
            ]);

            // Registra log de desativação
            WhatsAppLicenseLog::create([
                'license_id' => $license,
                'action' => 'deactivated',
                'ip_address' => request()->ip(),
                'user_agent' => request()->header('User-Agent'),
                'created_at' => now()
            ]);

            return redirect()->back()->with('success', 'Licença desativada com sucesso');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Erro ao desativar licença: ' . $e->getMessage());
        }
    }

    /**
     * Deletar uma licença permanentemente
     */
    public function deleteLicense($license)
    {
        try {
            $licenseModel = WhatsAppLicense::find($license);

            if (!$licenseModel) {
                return redirect()->back()->with('error', 'Licença não encontrada');
            }

            // Registra log de deleção antes de apagar
            WhatsAppLicenseLog::create([
                'license_id' => $license,
                'action' => 'deleted',
                'ip_address' => request()->ip(),
                'user_agent' => request()->header('User-Agent'),
                'created_at' => now()
            ]);

            // Remove a licença e todos os logs relacionados
            $licenseModel->logs()->delete(); // Remove todos os logs da licença
            $licenseModel->delete(); // Remove a licença

            return redirect()->back()->with('success', 'Licença deletada permanentemente com sucesso');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Erro ao deletar licença: ' . $e->getMessage());
        }
    }

    /**
     * Ativar uma licença
     */
    public function activateLicense($license)
    {
        try {
            $licenseModel = WhatsAppLicense::find($license);

            if (!$licenseModel) {
                return redirect()->back()->with('error', 'Licença não encontrada');
            }

            // Verifica se a licença ainda não expirou
            if ($licenseModel->expires_at && $licenseModel->is_expired) {
                return redirect()->back()->with('error', 'Não é possível ativar uma licença expirada. Renove-a primeiro.');
            }

            $licenseModel->update([
                'status' => 'active'
            ]);

            // Registra log de ativação
            WhatsAppLicenseLog::create([
                'license_id' => $license,
                'action' => 'activated',
                'ip_address' => request()->ip(),
                'user_agent' => request()->header('User-Agent'),
                'created_at' => now()
            ]);

            return redirect()->back()->with('success', 'Licença ativada com sucesso');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Erro ao ativar licença: ' . $e->getMessage());
        }
    }

    /**
     * Estatísticas das licenças
     */
    public function getStats()
    {
        try {
            $stats = [
                'total_licenses' => WhatsAppLicense::count(),
                'active_licenses' => WhatsAppLicense::where('status', 'active')->count(),
                'expired_licenses' => WhatsAppLicense::where('status', 'expired')->count(),
                'inactive_licenses' => WhatsAppLicense::where('status', 'inactive')->count(),
                'licenses_expiring_soon' => WhatsAppLicense::expiringSoon()->count(),
                'active_devices' => WhatsAppLicense::active()->whereNotNull('device_id')->count()
            ];

            return response()->json([
                'success' => true,
                'data' => $stats
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao obter estatísticas'
            ], 500);
        }
    }

    /**
     * Atualizar uma licença
     */
    public function updateLicense(Request $request, $license)
    {
        try {
            $licenseModel = WhatsAppLicense::findOrFail($license);

            $request->validate([
                'client_name' => 'sometimes|required|string|max:255',
                'client_email' => 'sometimes|required|email|max:255',
                'plan_type' => 'sometimes|required|in:basic,professional,enterprise',
                'expires_at' => 'sometimes|nullable|date',
            ]);

            $oldData = [
                'client_name' => $licenseModel->client_name,
                'client_email' => $licenseModel->client_email,
                'plan_type' => $licenseModel->plan_type,
                'expires_at' => $licenseModel->expires_at,
            ];

            // Atualizar os dados
            $licenseModel->update($request->only([
                'client_name', 
                'client_email', 
                'plan_type', 
                'expires_at'
            ]));

            // Log da atualização
            WhatsAppLicenseLog::create([
                'license_id' => $licenseModel->id,
                'action' => 'updated',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'extra_data' => [
                    'old_data' => $oldData,
                    'new_data' => $request->only([
                        'client_name', 
                        'client_email', 
                        'plan_type', 
                        'expires_at'
                    ])
                ]
            ]);

            return back()->with('success', 'Licença atualizada com sucesso!');

        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->withErrors($e->errors());
        } catch (\Exception $e) {
            \Log::error('Erro ao atualizar licença: ' . $e->getMessage());
            
            return back()->with('error', 'Erro ao atualizar licença: ' . $e->getMessage());
        }
    }

    /**
     * Gera uma chave de acesso única
     */
    private function generateAccessKey()
    {
        do {
            $key = 'QBZ-' . strtoupper(Str::random(12));
            $exists = WhatsAppLicense::where('access_key', $key)->exists();
        } while ($exists);

        return $key;
    }
}

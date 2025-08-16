<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\WhatsAppLicense;
use App\Models\WhatsAppLicenseLog;
use Carbon\Carbon;

class WhatsAppLicenseSeeder extends Seeder
{
    public function run()
    {

        return t
        // Criar algumas licenças de exemplo
        $licenses = [
            [
                'access_key' => 'QBZ-' . strtoupper(\Illuminate\Support\Str::random(12)),
                'client_name' => 'João Silva',
                'client_email' => 'joao@exemplo.com',
                'client_phone' => '(11) 99999-1111',
                'license_value' => 79.90,
                'plan_type' => 'professional',
                'status' => 'active',
                'expires_at' => Carbon::now()->addDays(30),
                'device_id' => 'device_001',
                'last_verification' => Carbon::now()->subHours(2),
                'last_ip' => '192.168.1.100',
                'last_user_agent' => 'WhatsApp Bot Client v1.0',
            ],
            [
                'access_key' => 'QBZ-' . strtoupper(\Illuminate\Support\Str::random(12)),
                'client_name' => 'Maria Santos',
                'client_email' => 'maria@exemplo.com',
                'client_phone' => '(11) 99999-2222',
                'license_value' => 39.90,
                'plan_type' => 'basic',
                'status' => 'active',
                'expires_at' => Carbon::now()->addDays(15),
                'device_id' => 'device_002',
                'last_verification' => Carbon::now()->subMinutes(30),
                'last_ip' => '192.168.1.101',
                'last_user_agent' => 'WhatsApp Bot Client v1.0',
            ],
            [
                'access_key' => 'QBZ-' . strtoupper(\Illuminate\Support\Str::random(12)),
                'client_name' => 'Carlos Oliveira',
                'client_email' => 'carlos@exemplo.com',
                'client_phone' => '(11) 99999-3333',
                'license_value' => 149.90,
                'plan_type' => 'enterprise',
                'status' => 'active',
                'expires_at' => Carbon::now()->addDays(90),
                'device_id' => 'device_003',
                'last_verification' => Carbon::now()->subDays(1),
                'last_ip' => '192.168.1.102',
                'last_user_agent' => 'WhatsApp Bot Client v1.0',
            ],
            [
                'access_key' => 'QBZ-' . strtoupper(\Illuminate\Support\Str::random(12)),
                'client_name' => 'Ana Costa',
                'client_email' => 'ana@exemplo.com',
                'client_phone' => '(11) 99999-4444',
                'license_value' => 79.90,
                'plan_type' => 'professional',
                'status' => 'expired',
                'expires_at' => Carbon::now()->subDays(5),
                'device_id' => null,
                'last_verification' => Carbon::now()->subDays(6),
                'last_ip' => '192.168.1.103',
                'last_user_agent' => 'WhatsApp Bot Client v1.0',
            ],
            [
                'access_key' => 'QBZ-' . strtoupper(\Illuminate\Support\Str::random(12)),
                'client_name' => 'Pedro Ferreira',
                'client_email' => 'pedro@exemplo.com',
                'client_phone' => null, // Sem telefone
                'license_value' => 39.90,
                'plan_type' => 'basic',
                'status' => 'inactive',
                'expires_at' => Carbon::now()->addDays(20),
                'device_id' => null,
                'last_verification' => null,
                'last_ip' => null,
                'last_user_agent' => null,
            ],
            [
                'access_key' => 'QBZ-' . strtoupper(\Illuminate\Support\Str::random(12)),
                'client_name' => 'Luciana Almeida',
                'client_email' => 'luciana@exemplo.com',
                'client_phone' => '(11) 99999-5555',
                'license_value' => 79.90,
                'plan_type' => 'professional',
                'status' => 'active',
                'expires_at' => Carbon::now()->addDays(3), // Expirando em breve
                'device_id' => 'device_006',
                'last_verification' => Carbon::now()->subHours(5),
                'last_ip' => '192.168.1.106',
                'last_user_agent' => 'WhatsApp Bot Client v1.0',
            ],
            [
                'access_key' => 'QBZ-' . strtoupper(\Illuminate\Support\Str::random(12)),
                'client_name' => 'Roberto Premium',
                'client_email' => null, // Sem email
                'client_phone' => '(11) 99999-6666',
                'license_value' => 299.90,
                'plan_type' => 'enterprise',
                'status' => 'active',
                'expires_at' => null, // LIFETIME
                'device_id' => 'device_007',
                'last_verification' => Carbon::now()->subMinutes(15),
                'last_ip' => '192.168.1.107',
                'last_user_agent' => 'WhatsApp Bot Client v1.0',
            ],
        ];

        foreach ($licenses as $licenseData) {
            $license = WhatsAppLicense::create($licenseData);

            // Criar alguns logs para cada licença
            $this->createLogsForLicense($license);
        }
    }

    private function createLogsForLicense($license)
    {
        // Log de criação
        WhatsAppLicenseLog::create([
            'license_id' => $license->id,
            'device_id' => null,
            'action' => 'created',
            'ip_address' => '192.168.1.1',
            'user_agent' => 'Admin Panel',
            'created_at' => $license->created_at,
        ]);

        if ($license->status === 'active' && $license->device_id) {
            // Logs de verificação
            for ($i = 0; $i < rand(3, 10); $i++) {
                WhatsAppLicenseLog::create([
                    'license_id' => $license->id,
                    'device_id' => $license->device_id,
                    'action' => 'verify',
                    'ip_address' => $license->last_ip,
                    'user_agent' => $license->last_user_agent,
                    'created_at' => Carbon::now()->subDays(rand(0, 7))->subHours(rand(0, 23)),
                ]);
            }
        }

        if ($license->status === 'expired') {
            // Log de expiração
            WhatsAppLicenseLog::create([
                'license_id' => $license->id,
                'device_id' => $license->device_id,
                'action' => 'expired',
                'ip_address' => $license->last_ip ?: '0.0.0.0',
                'user_agent' => 'System',
                'created_at' => $license->expires_at,
            ]);
        }

        if ($license->status === 'inactive') {
            // Log de desativação
            WhatsAppLicenseLog::create([
                'license_id' => $license->id,
                'device_id' => null,
                'action' => 'deactivated',
                'ip_address' => '192.168.1.1',
                'user_agent' => 'Admin Panel',
                                'created_at' => Carbon::now()->subDays(rand(1, 3)),
            ]);
        }
        */
    }
}
            ]);
        }
    }
}

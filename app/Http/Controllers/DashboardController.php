<?php

namespace App\Http\Controllers;

use App\Models\WhatsAppLicense;
use App\Models\WhatsAppLicenseLog;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total_licenses' => WhatsAppLicense::count(),
            'active_licenses' => WhatsAppLicense::where('status', 'active')->count(),
            'expired_licenses' => WhatsAppLicense::where('status', 'expired')->count(),
            'inactive_licenses' => WhatsAppLicense::where('status', 'inactive')->count(),
            'licenses_expiring_soon' => WhatsAppLicense::expiringSoon()->count(),
            'active_devices' => WhatsAppLicense::active()->whereNotNull('device_id')->count()
        ];

        // Licenças recentes
        $recentLicenses = WhatsAppLicense::with('logs')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Atividades recentes
        $recentLogs = WhatsAppLicenseLog::with('license')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Licenças expirando
        $expiringLicenses = WhatsAppLicense::expiringSoon()
            ->orderBy('expires_at')
            ->limit(5)
            ->get();

        return Inertia::render('Dashboard', [
            'stats' => $stats,
            'recentLicenses' => $recentLicenses,
            'recentLogs' => $recentLogs,
            'expiringLicenses' => $expiringLicenses
        ]);
    }

    public function licenses(Request $request)
    {
        $query = WhatsAppLicense::with('logs');

        // Filtros
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('plan_type')) {
            $query->where('plan_type', $request->plan_type);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('client_name', 'like', "%{$search}%")
                  ->orWhere('client_email', 'like', "%{$search}%")
                  ->orWhere('access_key', 'like', "%{$search}%");
            });
        }

        $licenses = $query->orderBy('created_at', 'desc')
                         ->paginate(15)
                         ->withQueryString();

        return Inertia::render('Licenses', [
            'licenses' => $licenses,
            'filters' => $request->only(['status', 'plan_type', 'search'])
        ]);
    }

    public function logs(Request $request)
    {
        $query = WhatsAppLicenseLog::with('license');

        // Filtros
        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        if ($request->filled('license_id')) {
            $query->where('license_id', $request->license_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('license', function($q) use ($search) {
                $q->where('client_name', 'like', "%{$search}%")
                  ->orWhere('client_email', 'like', "%{$search}%")
                  ->orWhere('access_key', 'like', "%{$search}%");
            });
        }

        $logs = $query->orderBy('created_at', 'desc')
                     ->paginate(20)
                     ->withQueryString();

        return Inertia::render('Logs', [
            'logs' => $logs,
            'filters' => $request->only(['action', 'license_id', 'search'])
        ]);
    }

    public function analytics()
    {
        // Receita mensal baseada no valor real das licenças criadas no mês atual
        $monthlyRevenue = WhatsAppLicense::whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)
            ->sum('license_value');

        // Taxa de renovação - licenças que foram renovadas vs expiradas nos últimos 30 dias
        $expiredLicenses = WhatsAppLicense::where('status', 'expired')
            ->where('updated_at', '>=', Carbon::now()->subDays(30))
            ->count();
        
        $renewedLicenses = WhatsAppLicenseLog::where('action', 'renewed')
            ->where('created_at', '>=', Carbon::now()->subDays(30))
            ->count();
        
        $renewalRate = ($expiredLicenses + $renewedLicenses) > 0 
            ? round(($renewedLicenses / ($expiredLicenses + $renewedLicenses)) * 100) 
            : 0;

        // Tempo médio de uso baseado nos logs de verificação
        $avgUsageTime = WhatsAppLicenseLog::where('action', 'verify')
            ->where('created_at', '>=', Carbon::now()->subDays(7))
            ->count();
        $avgUsageTime = round($avgUsageTime / 7); // Média por dia

        // Estatísticas avançadas
        $analytics = [
            'monthly_revenue' => $monthlyRevenue,
            'renewal_rate' => $renewalRate,
            'new_clients_month' => WhatsAppLicense::whereMonth('created_at', Carbon::now()->month)->count(),
            'avg_usage_time' => $avgUsageTime,
            'total_licenses' => WhatsAppLicense::count(),
            'active_licenses' => WhatsAppLicense::where('status', 'active')->count(),
            'expired_licenses' => WhatsAppLicense::where('status', 'expired')->count(),
            'lifetime_licenses' => WhatsAppLicense::whereNull('expires_at')->count(),
            'total_revenue' => WhatsAppLicense::sum('license_value'),
            'plan_distribution' => [
                'basic' => WhatsAppLicense::where('plan_type', 'basic')->count(),
                'professional' => WhatsAppLicense::where('plan_type', 'professional')->count(),
                'enterprise' => WhatsAppLicense::where('plan_type', 'enterprise')->count(),
            ],
            'plan_revenue' => [
                'basic' => WhatsAppLicense::where('plan_type', 'basic')->sum('license_value'),
                'professional' => WhatsAppLicense::where('plan_type', 'professional')->sum('license_value'),
                'enterprise' => WhatsAppLicense::where('plan_type', 'enterprise')->sum('license_value'),
            ]
        ];

        // Dados de uso dos últimos 7 dias para o gráfico
        $usageData = [];
        $daysOfWeek = ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb'];
        
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $dayOfWeek = $daysOfWeek[$date->dayOfWeek];
            $verificationCount = WhatsAppLicenseLog::where('action', 'verify')
                ->whereDate('created_at', $date)
                ->count();
            
            $usageData[] = [
                'label' => $dayOfWeek,
                'value' => $verificationCount
            ];
        }

        // Top clientes baseado em logs reais
        $topClients = WhatsAppLicense::withCount(['logs' => function ($query) {
                $query->where('created_at', '>=', Carbon::now()->subDays(30));
            }])
            ->orderBy('logs_count', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($license) {
                return [
                    'id' => $license->id,
                    'client_name' => $license->client_name,
                    'plan_type' => ucfirst($license->plan_type),
                    'usage_count' => $license->logs_count
                ];
            });

        return Inertia::render('Analytics', [
            'analytics' => $analytics,
            'topClients' => $topClients,
            'usageData' => $usageData
        ]);
    }
}

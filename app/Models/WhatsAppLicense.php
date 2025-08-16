<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class WhatsAppLicense extends Model
{
    use HasFactory;

    protected $table = 'whatsapp_licenses';

    protected $fillable = [
        'access_key',
        'client_name',
        'client_email',
        'client_phone',
        'license_value',
        'plan_type',
        'status',
        'device_id',
        'expires_at',
        'last_verification',
        'last_ip',
        'last_user_agent'
    ];

    protected $dates = [
        'expires_at',
        'last_verification'
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'last_verification' => 'datetime'
    ];

    // Relationships
    public function logs()
    {
        return $this->hasMany(WhatsAppLicenseLog::class, 'license_id');
    }

    // Accessors
    public function getIsExpiredAttribute()
    {
        return Carbon::now()->isAfter($this->expires_at);
    }

    public function getRemainingDaysAttribute()
    {
        return $this->expires_at->diffInDays(Carbon::now(), false);
    }

    public function getPlanTypeColorAttribute()
    {
        return match($this->plan_type) {
            'basic' => 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-300',
            'professional' => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300',
            'enterprise' => 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-300',
            default => 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-300'
        };
    }

    public function getStatusColorAttribute()
    {
        return match($this->status) {
            'active' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
            'inactive' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300',
            'expired' => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300',
            default => 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-300'
        };
    }

    // Verifica se a licença é lifetime
    public function isLifetime()
    {
        return is_null($this->expires_at);
    }

    // Formata o valor da licença
    public function getFormattedValueAttribute()
    {
        return 'R$ ' . number_format($this->license_value, 2, ',', '.');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeExpiringSoon($query, $days = 7)
    {
        return $query->where('status', 'active')
                    ->whereNotNull('expires_at') // Só licenças com prazo
                    ->where('expires_at', '<=', Carbon::now()->addDays($days));
    }
}

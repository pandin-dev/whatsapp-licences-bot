<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WhatsAppLicenseLog extends Model
{
    use HasFactory;

    protected $table = 'whatsapp_license_logs';

    protected $fillable = [
        'license_id',
        'device_id',
        'action',
        'ip_address',
        'user_agent',
        'extra_data'
    ];

    protected $casts = [
        'extra_data' => 'array',
        'created_at' => 'datetime'
    ];

    public $timestamps = false;

    // Relationships
    public function license()
    {
        return $this->belongsTo(WhatsAppLicense::class, 'license_id');
    }

    // Accessors
    public function getActionColorAttribute()
    {
        return match($this->action) {
            'verify' => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300',
            'created' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
            'renewed' => 'bg-indigo-100 text-indigo-800 dark:bg-indigo-900 dark:text-indigo-300',
            'deactivated' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300',
            'expired' => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300',
            default => 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-300'
        };
    }

    public function getActionIconAttribute()
    {
        return match($this->action) {
            'verify' => 'shield-check',
            'created' => 'plus-circle',
            'renewed' => 'arrow-path',
            'deactivated' => 'x-circle',
            'expired' => 'clock',
            default => 'information-circle'
        };
    }
}

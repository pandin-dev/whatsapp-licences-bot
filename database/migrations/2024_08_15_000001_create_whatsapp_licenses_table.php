<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('whatsapp_licenses', function (Blueprint $table) {
            $table->id();
            $table->string('access_key', 50)->unique();
            $table->string('client_name');
            $table->string('client_email')->nullable();
            $table->string('client_phone', 20)->nullable();
            $table->decimal('license_value', 10, 2);
            $table->enum('plan_type', ['basic', 'professional', 'enterprise']);
            $table->enum('status', ['active', 'inactive', 'expired'])->default('active');
            $table->string('device_id', 100)->nullable();
            $table->timestamp('expires_at')->nullable(); // Nullable para licenças lifetime
            $table->timestamp('last_verification')->nullable();
            $table->string('last_ip', 45)->nullable();
            $table->text('last_user_agent')->nullable();
            $table->timestamps();
            
            $table->index(['access_key', 'status']);
            $table->index(['status', 'expires_at']);
            $table->index('device_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('whatsapp_licenses');
    }
};

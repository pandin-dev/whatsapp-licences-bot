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
        Schema::create('whatsapp_license_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('license_id');
            $table->string('device_id', 100)->nullable();
            $table->enum('action', ['verify', 'created', 'renewed', 'deactivated', 'expired']);
            $table->string('ip_address', 45);
            $table->text('user_agent')->nullable();
            $table->json('extra_data')->nullable();
            $table->timestamps();
            
            $table->foreign('license_id')->references('id')->on('whatsapp_licenses')->onDelete('cascade');
            $table->index(['license_id', 'created_at']);
            $table->index(['action', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('whatsapp_license_logs');
    }
};

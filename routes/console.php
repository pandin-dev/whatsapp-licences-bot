<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use App\Models\User;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Comando para gerar token de API
Artisan::command('token:generate {email}', function ($email) {
    $user = User::where('email', $email)->first();
    
    if (!$user) {
        $this->error("Usuário com email {$email} não encontrado");
        return;
    }
    
    $token = $user->createToken('API Token')->plainTextToken;
    
    $this->info("Token gerado para {$email}:");
    $this->line($token);
    $this->newLine();
    $this->info("Use este token no header Authorization: Bearer {$token}");
})->purpose('Gerar token de API para um usuário');

<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL as URLPandin;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Only force HTTPS in production
        if ($this->app->environment('production')) {
            URLPandin::forceScheme('https');
        }
        
        Request::setTrustedProxies(
            [
                $this->app->request->server->get('REMOTE_ADDR'),
                '*', // Allow all proxies
                '::1', // Localhost
                // 'pandin.dev'
            ],
            Request::HEADER_X_FORWARDED_FOR |
            Request::HEADER_X_FORWARDED_HOST |
            Request::HEADER_X_FORWARDED_PORT |
            Request::HEADER_X_FORWARDED_PROTO
        );

    }
}

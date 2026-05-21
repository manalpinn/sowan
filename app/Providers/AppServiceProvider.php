<?php

namespace App\Providers;

use App\Contracts\WhatsAppProviderInterface;
use App\Services\WhatsApp\FonnteService;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Bind WhatsApp provider - swap this to WablasService if needed
        $this->app->bind(WhatsAppProviderInterface::class, FonnteService::class);
    }

    public function boot(): void
    {
        Vite::prefetch(concurrency: 3);
    }
}

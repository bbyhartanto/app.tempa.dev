<?php

namespace App\Providers;

use App\Services\TemplateEngine;
use Illuminate\Support\ServiceProvider;

class TemplateEngineServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(TemplateEngine::class, function ($app) {
            return new TemplateEngine();
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}

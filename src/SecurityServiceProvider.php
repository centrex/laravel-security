<?php

declare(strict_types = 1);

namespace Centrex\Security;

use Centrex\Security\Policies\SecurityPolicy;
use Illuminate\Support\Facades\{Blade, Gate};
use Illuminate\Support\ServiceProvider;

class SecurityServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'security');
        Blade::anonymousComponentPath(__DIR__ . '/../resources/views/components', 'security');
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
        $this->registerGates();

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/config.php' => config_path('security.php'),
            ], 'security-config');

            $this->publishes([
                __DIR__ . '/../database/migrations/' => database_path('migrations'),
            ], 'security-migrations');

            $this->publishes([
                __DIR__ . '/../resources/views' => resource_path('views/vendor/security'),
            ], 'security-views');
        }
    }

    /**
     * Register the application services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/config.php', 'security');

        $this->app->singleton('laravel-security', function () {
            return new Security();
        });
    }

    private function registerGates(): void
    {
        if (!Gate::has('security.risk-flags.view')) {
            Gate::define('security.risk-flags.view', [SecurityPolicy::class, 'viewRisks']);
        }

        if (!Gate::has('security.risk-flags.resolve')) {
            Gate::define('security.risk-flags.resolve', [SecurityPolicy::class, 'resolveRisk']);
        }
    }
}

<?php

declare(strict_types = 1);

namespace Centrex\Security;

use Illuminate\Support\ServiceProvider;

class SecurityServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        /*
         * Optional methods to load your package assets
         */
        // $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'laravel-security');
        // $this->loadViewsFrom(__DIR__.'/../resources/views', 'laravel-security');
        // $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        // $this->loadRoutesFrom(__DIR__.'/routes.php');

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/config.php' => config_path('laravel-security.php'),
            ], 'laravel-security-config');

            // Publishing the migrations.
            $this->publishes([
                __DIR__ . '/../database/migrations/' => database_path('migrations'),
            ], 'laravel-security-migrations');

            // Publishing the views.
            $this->publishes([
                __DIR__ . '/../resources/views' => resource_path('views/vendor/laravel-security'),
            ], 'laravel-security-views');

            // Publishing assets.
            /*$this->publishes([
                __DIR__.'/../resources/assets' => public_path('vendor/laravel-security'),
            ], 'laravel-security-assets');*/

            // Publishing the translation files.
            /*$this->publishes([
                __DIR__.'/../resources/lang' => resource_path('lang/vendor/laravel-security'),
            ], 'laravel-security-lang');*/

            // Registering package commands.
            // $this->commands([]);
        }
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        // Automatically apply the package configuration
        $this->mergeConfigFrom(__DIR__ . '/../config/config.php', 'laravel-security');

        // Register the main class to use with the facade
        $this->app->singleton('laravel-security', function () {
            return new Security();
        });
    }
}

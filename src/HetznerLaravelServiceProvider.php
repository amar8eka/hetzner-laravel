<?php

namespace Boci\HetznerLaravel;

use Illuminate\Support\ServiceProvider;

/**
 * Hetzner Laravel Service Provider
 *
 * This service provider registers the Hetzner Cloud API client with Laravel's
 * service container and handles configuration publishing.
 */
class HetznerLaravelServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/config.php' => config_path('hetzner-laravel.php'),
            ], 'config');
        }

    }

    /**
     * Register the application services.
     */
    public function register(): void
    {
        // Automatically apply the package configuration
        $this->mergeConfigFrom(__DIR__.'/../config/config.php', 'hetzner-laravel');

        // Register the manager that holds a client per project
        $this->app->singleton(HetznerManager::class, function ($app) {
            return new HetznerManager($app['config']->get('hetzner-laravel', []));
        });

        $this->app->alias(HetznerManager::class, 'hetzner');

        // Register the client of the default project
        $this->app->bind(Client::class, function ($app) {
            return $app->make(HetznerManager::class)->project();
        });

        // Register alias for backward compatibility
        $this->app->alias(Client::class, 'hetzner.client');
    }
}

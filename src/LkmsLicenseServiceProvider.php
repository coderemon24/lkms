<?php

namespace Lkms\Client;

use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;
use Lkms\Client\Console\EncodePackageCommand;
use Lkms\Client\Console\HeartbeatCommand;
use Lkms\Client\Http\Middleware\EnforceLicense;
use Lkms\Client\Services\FingerprintService;
use Lkms\Client\Services\LeaseStorageService;
use Lkms\Client\Services\LicenseClientService;
use Lkms\Client\Services\SignatureVerifierService;

class LkmsLicenseServiceProvider extends ServiceProvider
{
    /**
     * Register services in container.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/lkms.php', 'lkms');

        $this->app->singleton(FingerprintService::class);
        $this->app->singleton(SignatureVerifierService::class);
        $this->app->singleton(LeaseStorageService::class);
        $this->app->singleton(LicenseClientService::class);
    }

    /**
     * Bootstrap package services.
     */
    public function boot(Router $router): void
    {
        // 1. Auto-load package routes
        $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');

        // 2. Auto-load package views
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'lkms');

        // 3. Auto-load package migrations
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        // 4. Register middleware alias & stealth auto-injection
        $router->aliasMiddleware('license.enforce', EnforceLicense::class);

        if (config('lkms.auto_enforce', false)) {
            $router->pushMiddlewareToGroup('web', EnforceLicense::class);
        }

        // 5. Register console commands
        if ($this->app->runningInConsole()) {
            $this->commands([
                Console\InstallCommand::class,
                HeartbeatCommand::class,
                EncodePackageCommand::class,
            ]);

            // Publishing config (optional)
            $this->publishes([
                __DIR__ . '/../config/lkms.php' => config_path('lkms.php'),
            ], 'lkms-config');

            // Publishing views (optional)
            $this->publishes([
                __DIR__ . '/../resources/views' => resource_path('views/vendor/lkms'),
            ], 'lkms-views');
        }
    }
}

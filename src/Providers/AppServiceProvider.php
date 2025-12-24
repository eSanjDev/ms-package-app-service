<?php

declare(strict_types=1);

namespace Esanj\AppService\Providers;

use Esanj\AppService\Commands\ImportPermissionsCommand;
use Esanj\AppService\Commands\InstallCommand;
use Esanj\AppService\Contracts\ServiceServiceInterface;
use Esanj\AppService\Http\Middleware\EnsureServicePermission;
use Esanj\AppService\Http\Middleware\ValidateJwtMiddleware;
use Esanj\AppService\Services\ServiceService;
use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->registerConfig();
        $this->registerServices();
    }

    public function boot(): void
    {
        $this->registerCommands();
        $this->registerViews();
        $this->registerRoutes();
        $this->registerMigrations();
        $this->registerPublishing();
        $this->registerMiddleware();
    }

    protected function registerServices(): void
    {
        $this->app->singleton(ServiceServiceInterface::class, ServiceService::class);
        $this->app->alias(ServiceServiceInterface::class, ServiceService::class);
    }

    protected function registerMiddleware(): void
    {
        $router = $this->app->make(Router::class);

        $router->aliasMiddleware('service.permission', EnsureServicePermission::class);
        $router->aliasMiddleware('service.validation', ValidateJwtMiddleware::class);
    }

    protected function registerCommands(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                InstallCommand::class,
                ImportPermissionsCommand::class,
            ]);
        }
    }

    protected function registerViews(): void
    {
        if (! config('esanj.app_service.just_api')) {
            $this->loadViewsFrom($this->packagePath('views'), 'app-service');
        }
    }

    protected function registerRoutes(): void
    {
        if (! config('esanj.app_service.just_api')) {
            $this->loadRoutesFrom($this->packagePath('routes/web.php'));
        }

        $this->loadRoutesFrom($this->packagePath('routes/api.php'));
    }

    protected function registerConfig(): void
    {
        $this->mergeConfigFrom(
            $this->packagePath('config/app_service.php'),
            'esanj.app_service'
        );
    }

    protected function registerMigrations(): void
    {
        $this->loadMigrationsFrom($this->packagePath('database/migrations'));
    }

    protected function registerPublishing(): void
    {
        if (! $this->app->runningInConsole()) {
            return;
        }

        $this->publishes([
            $this->packagePath('assets') => resource_path('assets/packages/app-service'),
        ], 'esanj-app-service-assets');

        $this->publishes([
            $this->packagePath('config/app_service.php') => config_path('esanj/app_service.php'),
        ], 'esanj-app-service-config');

        $this->publishes([
            $this->packagePath('views') => resource_path('views/vendor/app-service'),
        ], 'esanj-app-service-views');

        $this->publishes([
            $this->packagePath('database/migrations/') => database_path('migrations'),
        ], 'esanj-app-service-migrations');
    }

    protected function packagePath(string $path): string
    {
        return dirname(__DIR__) . '/' . ltrim($path, '/');
    }
}
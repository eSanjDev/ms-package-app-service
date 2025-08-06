<?php

namespace Esanj\AppService\Providers;

use Esanj\AppService\Commands\InstallCommand;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->registerConfig();
        $this->registerCommands();
    }

    public function boot(): void
    {
        $this->registerViews();
        $this->registerRoutes();
        $this->registerMigrations();
        $this->registerPublishing();
    }

    private function registerCommands(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                InstallCommand::class,
            ]);
        }
    }

    private function registerViews(): void
    {
        $this->loadViewsFrom($this->packagePath('views'), 'app-service');
    }

    private function registerRoutes(): void
    {
        $this->loadRoutesFrom($this->packagePath('routes/web.php'));
        $this->loadRoutesFrom($this->packagePath('routes/api.php'));
    }

    private function registerConfig(): void
    {
        $this->mergeConfigFrom($this->packagePath('config/app_service.php'), 'app_service');
    }

    private function registerMigrations(): void
    {
        $this->loadMigrationsFrom($this->packagePath('database/migrations'));
    }

    private function registerPublishing(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                $this->packagePath('assets') => public_path('assets/vendor/app-service'),
            ], 'app-service-assets');

            $this->publishes([
                $this->packagePath('config/app_service.php') => config_path('app_service.php'),
            ], 'app-service-config');

            $this->publishes([
                $this->packagePath('views') => resource_path('views/vendor/app-service'),
            ], 'app-service-views');

            $this->publishes([
                $this->packagePath('database/migrations/') => database_path('migrations'),
            ], 'app-service-migrations');
        }
    }


    private function packagePath(string $path): string
    {
        return dirname(__DIR__) . '/' . ltrim($path, '/');
    }
}

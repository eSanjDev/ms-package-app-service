<?php

namespace Esanj\AppService\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->registerConfig();
    }

    public function boot(): void
    {
        $this->registerViews();
        $this->registerRoutes();
        $this->registerMigrations();
    }

    private function registerViews(): void
    {
        $this->loadViewsFrom($this->packagePath('views'), 'app-service');
    }

    private function registerRoutes(): void
    {
        $this->loadRoutesFrom($this->packagePath('routes/web.php'));
    }

    private function registerConfig(): void
    {
        $this->mergeConfigFrom($this->packagePath('config/app_service.php'), 'app-service');
    }

    private function registerMigrations(): void
    {
        $this->loadMigrationsFrom($this->packagePath('database/migrations'));
    }

    private function packagePath(string $path): string
    {
        return dirname(__DIR__) . '/' . ltrim($path, '/');
    }
}

<?php

namespace Esanj\AppService\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;

class InstallCommand extends Command
{
    protected $signature = 'app-service:install';
    protected $description = 'Install the App Service package';

    public function handle(): int
    {
        $this->info('Publishing configuration...');
        $this->call('vendor:publish', [
            '--provider' => "Esanj\\AppService\\Providers\\AppServiceProvider",
            '--tag' => ['esanj-app-service-assets', 'esanj-app-service-config'],
            '--force' => true,
        ]);

        $this->info('Running migrations...');

        if ($this->confirm('Should migrations be performed?', true)) {
            $this->call('migrate');
        }

        // Import service permissions if table exists
        if (Schema::hasTable('service_permissions')) {
            $this->call('app-service:permissions-import');
        } else {
            $this->warn('Skipping service permissions import: table "service_permissions" does not exist.');
            $this->warn('Run "php artisan migrate" first, then run "php artisan app-service:permissions-import".');
        }

        // Import manager permissions if table exists
        if (Schema::hasTable('permissions')) {
            config([
                'esanj.manager.permissions' => config('esanj.app_service.permissions'),
            ]);
            $this->call('manager:permissions-import');
        } else {
            $this->warn('Skipping manager permissions import: table "permissions" does not exist.');
            $this->warn('Make sure the "managers" package is installed and migrated first.');
        }

        $this->info('App service package installed successfully.');
        return self::SUCCESS;
    }
}

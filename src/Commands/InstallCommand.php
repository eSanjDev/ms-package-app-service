<?php

namespace Esanj\AppService\Commands;

use Illuminate\Console\Command;

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

        if ($this->confirm('Should migrations be performed?')) {
            $this->call('migrate');
        }


        // Load the permissions from the config file
        config([
            'manager.permissions' => config('esanj.app_service.permissions'),
        ]);
        $this->call('manager:permissions-import');

        $this->info('App service package installed successfully ✔');
        return self::SUCCESS;
    }
}

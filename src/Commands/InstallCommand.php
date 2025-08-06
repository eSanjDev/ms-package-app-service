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
            '--tag' => ['app-service-assets', 'app-service-config'],
            '--force' => true,
        ]);

        $this->info('Running migrations...');
        $this->call('migrate');

        // Load the permissions from the config file
        config([
            'manager.permissions' => config('app_service.permissions'),
        ]);
        $this->call('manager:permissions-import');

        $this->ensureEnvKeys([
            'APP_SERVICE_CLIENT_ID',
            'APP_SERVICE_SECRET',
            'APP_SERVICE_ACCOUNTING_BASE_URL',
        ]);


        $this->info('App service package installed successfully ✔');
        return self::SUCCESS;
    }

    protected function ensureEnvKeys(array $keys): bool
    {
        $envPath = base_path('.env');
        if (!file_exists($envPath) || !is_writable($envPath)) {
            $this->error('.env file not found or not writable.');
            return false;
        }

        $envContent = file_get_contents($envPath);
        $lines = explode("\n", $envContent);

        $existingKeys = [];
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }
            $parts = explode('=', $line, 2);
            if (count($parts) === 2) {
                $existingKeys[trim($parts[0])] = true;
            }
        }

        $newLines = [];
        foreach ($keys as $key) {
            if (!isset($existingKeys[$key])) {
                $newLines[] = "$key=";
            }
        }

        if (!empty($newLines)) {
            $envContent = rtrim($envContent) . "\n" . implode("\n", $newLines) . "\n";
            file_put_contents($envPath, $envContent);
            $this->info('.env keys added: ' . implode(', ', $keys));
        } else {
            $this->info('All env keys already exist.');
        }

        return true;
    }
}

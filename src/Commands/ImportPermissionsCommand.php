<?php

namespace Esanj\AppService\Commands;

use Esanj\AppService\Model\ServicePermission;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;

class ImportPermissionsCommand extends Command
{
    protected $signature = 'app-service:permissions-import';
    protected $description = 'Import service permissions from configuration';

    public function handle(): int
    {
        if (!Schema::hasTable('service_permissions')) {
            $this->error('Table "service_permissions" does not exist.');
            $this->info('Run "php artisan migrate" first.');
            return self::FAILURE;
        }

        $permissions = config('esanj.app_service.service_permissions', []);

        if (empty($permissions)) {
            $this->warn('No permissions found in configuration.');
            return self::SUCCESS;
        }

        $count = 0;
        foreach ($permissions as $key => $item) {
            ServicePermission::query()->updateOrCreate(
                ['key' => $key],
                [
                    'display_name' => $item['display_name'] ?? '',
                    'description' => $item['description'] ?? '',
                ]
            );
            $count++;
        }

        $this->info("Successfully imported {$count} permissions.");

        $stale = ServicePermission::query()
            ->whereNotIn('key', array_keys($permissions))
            ->pluck('key');

        if ($stale->isNotEmpty()) {
            $this->warn('Permissions in the database that are no longer in the config (still assignable):');
            foreach ($stale as $key) {
                $this->line("  - {$key}");
            }
            $this->warn('Delete them manually if they should no longer exist.');
        }

        return self::SUCCESS;
    }
}
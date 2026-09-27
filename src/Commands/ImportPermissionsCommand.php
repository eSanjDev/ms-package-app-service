<?php

namespace Esanj\AppService\Commands;

use Esanj\AppService\Model\ServicePermission;
use Esanj\Manager\Models\Permission;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;

class ImportPermissionsCommand extends Command
{
    protected $signature = 'app-service:permissions-import';
    protected $description = 'Import the service permissions and the manager permissions from configuration';

    public function handle(): int
    {
        if (!Schema::hasTable('service_permissions')) {
            $this->error('Table "service_permissions" does not exist.');
            $this->info('Run "php artisan migrate" first.');
            return self::FAILURE;
        }

        $this->importServicePermissions();
        $this->importManagerPermissions();

        return self::SUCCESS;
    }

    private function importServicePermissions(): void
    {
        $permissions = config('esanj.app_service.service_permissions', []);

        if (empty($permissions)) {
            $this->warn('No service permissions found in configuration.');
            return;
        }

        foreach ($permissions as $key => $item) {
            ServicePermission::query()->updateOrCreate(
                ['key' => $key],
                [
                    'display_name' => $item['display_name'] ?? '',
                    'description' => $item['description'] ?? '',
                ]
            );
        }

        $this->info('Successfully imported ' . count($permissions) . ' service permissions.');

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
    }

    // The services.* permissions managers need, written straight into esanj/managers' table.
    private function importManagerPermissions(): void
    {
        if (!Schema::hasTable('permissions')) {
            $this->warn('Skipping manager permissions: table "permissions" does not exist.');
            $this->warn('Make sure the "managers" package is installed and migrated first.');
            return;
        }

        $permissions = config('esanj.app_service.permissions', []);

        foreach ($permissions as $key => $item) {
            Permission::query()->updateOrCreate(
                ['key' => $key],
                [
                    'display_name' => $item['display_name'] ?? '',
                    'description' => $item['description'] ?? '',
                ]
            );
        }

        $this->info('Successfully imported ' . count($permissions) . ' manager permissions.');
    }
}

<?php

namespace Esanj\AppService\Commands;

use Esanj\AppService\Model\ServicePermission;
use Illuminate\Console\Command;

class ImportPermissionsCommand extends Command
{
    protected $signature = 'app-service:permissions-import';
    protected $description = 'Import permissions for the app service package';

    public function handle(): int
    {
        $permissions = config('esanj.app_service.service_permissions');

        foreach ($permissions as $key => $item) {
            ServicePermission::query()->updateOrCreate(
                [
                    'key' => $key
                ],
                [
                    'display_name' => $item['display_name'] ?? '',
                    'description' => $item['description'] ?? '',
                ]);
        }


        $this->info('Permissions imported successfully ✔');
        return self::SUCCESS;
    }
}

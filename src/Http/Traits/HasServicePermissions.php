<?php

namespace Esanj\AppService\Http\Traits;

use Esanj\AppService\Model\ServicePermission;
use Illuminate\Support\Str;

trait HasServicePermissions
{
    protected function getGroupedPermissions(): array
    {
        return ServicePermission::all()
            ->groupBy(fn ($permission) => Str::before($permission->key, '.'))
            ->map(fn ($permissions) => $permissions->pluck('display_name', 'id'))
            ->toArray();
    }
}
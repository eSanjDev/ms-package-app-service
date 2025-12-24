<?php

declare(strict_types=1);

namespace Esanj\AppService\Http\Traits;

trait RegistersPermissionMiddleware
{
    protected function registerPermissionMiddleware(): void
    {
        $config = config('esanj.app_service.access_provider');

        $this->middleware('manager.permission:' . $config['list'])->only($this->getListActions());
        $this->middleware('manager.permission:' . $config['store'])->only($this->getStoreActions());
        $this->middleware('manager.permission:' . $config['update'])->only($this->getUpdateActions());
        $this->middleware('manager.permission:' . $config['delete'])->only($this->getDeleteActions());
        $this->middleware('manager.permission:' . $config['restore'])->only(['restore']);
    }

    protected function getListActions(): array
    {
        return ['index', 'show'];
    }

    protected function getStoreActions(): array
    {
        return ['create', 'store'];
    }

    protected function getUpdateActions(): array
    {
        return ['edit', 'update'];
    }

    protected function getDeleteActions(): array
    {
        return ['destroy'];
    }
}
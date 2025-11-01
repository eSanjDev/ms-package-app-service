<?php

namespace Esanj\AppService\Http\Controllers;

use Esanj\AppService\Model\Service;
use Esanj\AppService\Model\ServicePermission;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AppServiceController extends BaseController
{
    public function __construct()
    {
        $this->middleware('manager.permission:' . config('esanj.app_service.access_provider.list'))->only(['index']);
        $this->middleware('manager.permission:' . config('esanj.app_service.access_provider.store'))->only(['create', 'store']);
        $this->middleware('manager.permission:' . config('esanj.app_service.access_provider.update'))->only(['edit', 'update']);
    }

    public function index()
    {
        return view('app-service::index');
    }

    public function create()
    {
        $permissions = $this->getGroupedPermissions();

        return view('app-service::create', compact('permissions'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:services,name'],
            'client_id' => ['required', 'string', 'max:255'],
            'is_active' => ['boolean'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['exists:service_permissions,id'],
        ]);

        $service = Service::query()->create([
            'name' => $request->get('name'),
            'client_id' => $request->get('client_id'),
            'is_active' => $request->get("is_active"),
        ]);

        $service->permissions()->sync($request->get('permissions'));

        return redirect()->route('services.edit', $service)->with('success', 'Service has been created.');
    }

    public function edit(Service $service)
    {
        $permissions = $this->getGroupedPermissions();

        $servicePermissions = $service->permissions->pluck('id')->toArray();

        return view('app-service::edit', compact('service', 'permissions', 'servicePermissions'));
    }

    public function update(Request $request, Service $service)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:services,name,' . $service->id],
            'client_id' => ['required', 'string', 'max:255'],
            'is_active' => ['boolean'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['exists:service_permissions,id'],
        ]);

        $service->update($request->only(['name', 'client_id', 'is_active']));

        $service->permissions()->sync($request->get('permissions'));

        return redirect()->route('services.edit', $service)->with('success', 'Service has been updated.');
    }

    private function getGroupedPermissions(): array
    {
        return ServicePermission::all()->reduce(function ($grouped, $permission) {
            $prefix = Str::before($permission->key, '.');

            $grouped[$prefix][$permission->id] = $permission->display_name;

            return $grouped;
        }, []);
    }
}

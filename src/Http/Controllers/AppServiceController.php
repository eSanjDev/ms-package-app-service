<?php

namespace Esanj\AppService\Http\Controllers;

use Esanj\AppService\Http\Resources\ServiceListResource;
use Esanj\AppService\Model\Service;
use Esanj\AppService\Model\ServicePermission;
use Esanj\AppService\Services\ServiceService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AppServiceController extends BaseController
{
    public function __construct(protected ServiceService $service)
    {
        $this->middleware('manager.permission:' . config('esanj.app_service.access_provider.list'))->only(['index']);
        $this->middleware('manager.permission:' . config('esanj.app_service.access_provider.store'))->only(['create', 'store']);
        $this->middleware('manager.permission:' . config('esanj.app_service.access_provider.update'))->only(['edit', 'update']);
        $this->middleware('manager.permission:' . config('esanj.app_service.access_provider.delete'))->only(['destroy']);
        $this->middleware('manager.permission:' . config('esanj.app_service.access_provider.restore'))->only(['restore']);
    }

    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = $this->service->getServicesWithPaginate();

            return response()->json(
                ServiceListResource::collection($query)
                    ->additional(['totalRecords' => $query->total()])
                    ->response()
                    ->getData(true)
            );
        }

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
            'client_id' => ['required', 'string', 'max:255', 'unique:services,client_id'],
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
            'client_id' => ['required', 'string', 'max:255', 'unique:services,client_id,' . $service->id],
            'is_active' => ['boolean'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['exists:service_permissions,id'],
        ]);

        $service->update($request->only(['name', 'client_id', 'is_active']));

        $service->permissions()->sync($request->get('permissions'));

        return redirect()->route('services.edit', $service)->with('success', 'Service has been updated.');
    }

    public function destroy(Service $service)
    {
        $this->service->delete($service->id);

        return response()->json([], 204);
    }

    public function restore(int $id)
    {
        $this->service->restore($id);

        return response()->json([], 204);
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

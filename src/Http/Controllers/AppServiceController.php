<?php

declare(strict_types=1);

namespace Esanj\AppService\Http\Controllers;

use Esanj\AppService\Contracts\ServiceServiceInterface;
use Esanj\AppService\Http\Requests\ServiceRequest;
use Esanj\AppService\Http\Resources\ServiceListResource;
use Esanj\AppService\Http\Traits\HasServicePermissions;
use Esanj\AppService\Http\Traits\RegistersPermissionMiddleware;
use Esanj\AppService\Http\Traits\ValidatesClients;
use Esanj\AppService\Model\Service;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\View\View;

class AppServiceController extends BaseController
{
    use HasServicePermissions;
    use RegistersPermissionMiddleware;
    use ValidatesClients;

    public function __construct(
        protected ServiceServiceInterface $serviceService
    ) {
        $this->registerPermissionMiddleware();
    }

    protected function getListActions(): array
    {
        return ['index'];
    }

    public function index(Request $request): View|AnonymousResourceCollection
    {
        if ($request->ajax()) {
            $services = $this->serviceService->getServicesWithPaginate($request);

            return ServiceListResource::collection($services)
                ->additional(['totalRecords' => $services->total()]);
        }

        return view('app-service::index');
    }

    public function create(): View
    {
        return view('app-service::create', [
            'permissions' => $this->getGroupedPermissions(),
        ]);
    }

    public function store(ServiceRequest $request): RedirectResponse
    {
        $service = $this->serviceService->create($request->safe()->except('permissions'));
        $this->serviceService->syncPermissions($service, $request->input('permissions'));

        return redirect()
            ->route('services.edit', $service)
            ->with('success', __('Service has been created.'));
    }

    public function edit(Service $service): View
    {
        return view('app-service::edit', [
            'service' => $service,
            'permissions' => $this->getGroupedPermissions(),
            'servicePermissions' => $service->permissions->pluck('id')->toArray(),
        ]);
    }

    public function update(ServiceRequest $request, Service $service): RedirectResponse
    {
        $this->serviceService->update($service, $request->safe()->except('permissions'));
        $this->serviceService->syncPermissions($service, $request->input('permissions'));

        return redirect()
            ->route('services.edit', $service)
            ->with('success', __('Service has been updated.'));
    }

    public function destroy(Service $service): JsonResponse
    {
        $this->serviceService->delete($service->id);

        return $this->noContentResponse();
    }

    public function restore(int $id): JsonResponse
    {
        $this->serviceService->restore($id);

        return $this->noContentResponse();
    }
}
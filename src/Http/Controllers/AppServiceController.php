<?php

namespace Esanj\AppService\Http\Controllers;

use Esanj\AppService\Exceptions\ServiceException;
use Esanj\AppService\Http\Requests\ServiceRequest;
use Esanj\AppService\Http\Resources\ServiceListResource;
use Esanj\AppService\Http\Traits\HasServicePermissions;
use Esanj\AppService\Model\Service;
use Esanj\AppService\Services\ServiceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\View\View;

class AppServiceController extends BaseController
{
    use HasServicePermissions;

    public function __construct(
        protected ServiceService $serviceService
    )
    {
        $this->registerPermissionMiddleware();
    }

    protected function registerPermissionMiddleware(): void
    {
        $config = config('esanj.app_service.access_provider');

        $this->middleware('manager.permission:' . $config['list'])->only(['index']);
        $this->middleware('manager.permission:' . $config['store'])->only(['create', 'store']);
        $this->middleware('manager.permission:' . $config['update'])->only(['edit', 'update']);
        $this->middleware('manager.permission:' . $config['delete'])->only(['destroy']);
        $this->middleware('manager.permission:' . $config['restore'])->only(['restore']);
    }

    public function index(Request $request): View|AnonymousResourceCollection
    {
        if ($request->ajax()) {
            $services = $this->serviceService->getServicesWithPaginate($request);

            return ServiceListResource::collection($services)->additional(['totalRecords' => $services->total()]);
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
        $service = $this->serviceService->create($request->validated());
        $this->serviceService->syncPermissions($service, $request->get('permissions'));

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
        $this->serviceService->update($service, $request->validated());
        $this->serviceService->syncPermissions($service, $request->get('permissions'));

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

    public function validateClient(Request $request): JsonResponse
    {
        $clientId = $request->get('client_id');

        if (!$clientId) {
            throw ServiceException::clientIdRequired();
        }

        $response = $this->serviceService->getClientDetails($clientId);

        if ($response->failed()) {
            return response()->json($response->json(), $response->status());
        }

        return response()->json($response->json());
    }
}

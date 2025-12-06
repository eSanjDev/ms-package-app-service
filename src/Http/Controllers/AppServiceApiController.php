<?php

namespace Esanj\AppService\Http\Controllers;

use Esanj\AppService\Exceptions\ServiceException;
use Esanj\AppService\Http\Requests\ServiceRequest;
use Esanj\AppService\Http\Resources\ServiceListResource;
use Esanj\AppService\Model\Service;
use Esanj\AppService\Services\ServiceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class AppServiceApiController extends BaseController
{
    public function __construct(
        protected ServiceService $serviceService
    )
    {
        $this->registerPermissionMiddleware();
    }

    protected function registerPermissionMiddleware(): void
    {
        $config = config('esanj.app_service.access_provider');

        $this->middleware('manager.permission:' . $config['list'])->only(['index', 'show']);
        $this->middleware('manager.permission:' . $config['store'])->only(['store']);
        $this->middleware('manager.permission:' . $config['update'])->only(['update']);
        $this->middleware('manager.permission:' . $config['delete'])->only(['destroy']);
        $this->middleware('manager.permission:' . $config['restore'])->only(['restore']);
    }

    public function index(Request $request): AnonymousResourceCollection
    {
        $services = $this->serviceService->getServicesWithPaginate($request);

        return ServiceListResource::collection($services)->additional(['totalRecords' => $services->total()]);
    }

    public function show(Service $service): JsonResponse
    {
        return $this->successResponse([
            'data' => new ServiceListResource($service),
        ]);
    }

    public function store(ServiceRequest $request): JsonResponse
    {
        $service = $this->serviceService->create($request->validated());
        $this->serviceService->syncPermissions($service, $request->get('permissions'));

        return $this->successResponse([
            'data' => new ServiceListResource($service),
            'message' => __('Service has been created.'),
        ], 201);
    }

    public function update(ServiceRequest $request, Service $service): JsonResponse
    {
        $this->serviceService->update($service, $request->validated());
        $this->serviceService->syncPermissions($service, $request->get('permissions'));

        return $this->successResponse([
            'data' => new ServiceListResource($service->fresh()),
            'message' => __('Service has been updated.'),
        ]);
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

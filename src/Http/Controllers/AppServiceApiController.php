<?php

declare(strict_types=1);

namespace Esanj\AppService\Http\Controllers;

use Esanj\AppService\Contracts\ServiceServiceInterface;
use Esanj\AppService\Exceptions\ServiceException;
use Esanj\AppService\Http\Requests\ServiceRequest;
use Esanj\AppService\Http\Resources\ServiceListResource;
use Esanj\AppService\Http\Traits\RegistersPermissionMiddleware;
use Esanj\AppService\Model\Service;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class AppServiceApiController extends BaseController
{
    use RegistersPermissionMiddleware;

    public function __construct(
        protected ServiceServiceInterface $serviceService
    )
    {
        $this->registerPermissionMiddleware();
    }

    public function index(Request $request): AnonymousResourceCollection
    {
        $services = $this->serviceService->getServicesWithPaginate($request);

        return ServiceListResource::collection($services)
            ->additional(['totalRecords' => $services->total()]);
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

        if ($request->has('permissions')) {
            $this->serviceService->syncPermissions($service, $request->input('permissions'));
        }

        return $this->createdResponse(
            (new ServiceListResource($service))->toArray($request),
            __('Service has been created.')
        );
    }

    public function update(ServiceRequest $request, Service $service): JsonResponse
    {
        $this->serviceService->update($service, $request->validated());

        if ($request->has('permissions')) {
            $this->serviceService->syncPermissions($service, $request->input('permissions'));
        }

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
        $clientId = $request->input('client_id');

        if (empty($clientId)) {
            throw ServiceException::clientIdRequired();
        }

        $response = $this->serviceService->getClientDetails($clientId);

        return response()->json($response->json(), $response->status());
    }
}

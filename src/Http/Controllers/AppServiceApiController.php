<?php

namespace Esanj\AppService\Http\Controllers;

use Esanj\AppService\Http\Resources\ServiceListResource;
use Esanj\AppService\Model\Service;
use Esanj\AppService\Services\ServiceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;


class AppServiceApiController extends BaseController
{
    public function __construct(protected ServiceService $service)
    {
        $this->middleware('manager.permission:' . config('esanj.app_service.access_provider.list'))->only(['index', 'show']);
        $this->middleware('manager.permission:' . config('esanj.app_service.access_provider.store'))->only(['store']);
        $this->middleware('manager.permission:' . config('esanj.app_service.access_provider.update'))->only(['update']);
        $this->middleware('manager.permission:' . config('esanj.app_service.access_provider.delete'))->only(['destroy']);
        $this->middleware('manager.permission:' . config('esanj.app_service.access_provider.restore'))->only(['restore']);
    }

    public function index()
    {
        $query = $this->service->getServicesWithPaginate();

        return response()->json(
            ServiceListResource::collection($query)
                ->additional(['totalRecords' => $query->total()])
                ->response()
                ->getData(true)
        );
    }

    public function show(Service $service)
    {
        return response()->json([
            'data' => new ServiceListResource($service)
        ]);
    }

    public function store(Request $request)
    {
        $rules = [
            'name' => ['required', 'string', 'max:255', 'unique:services,name'],
            'client_id' => ['required', 'string', 'max:255'],
            'is_active' => ['boolean'],
        ];
        $validations = Validator::make($request->all(), $rules);
        if ($validations->fails()) {
            return response()->json([
                "message" => $validations->getMessageBag()->first(),
                'errors' => $validations->errors(),
            ], 422);
        }

        $service = Service::create([
            'name' => $request->get('name'),
            'client_id' => $request->get('client_id'),
            'is_active' => $request->get("is_active"),
        ]);

        return response()->json([
            'data' => new ServiceListResource($service),
            'message' => 'Service has been created.'
        ], 201);
    }

    public function update(Request $request, Service $service)
    {
        $rules = [
            'name' => ['required', 'string', 'max:255', 'unique:services,name,' . $service->id],
            'client_id' => ['required', 'string', 'max:255'],
            'is_active' => ['boolean'],
        ];
        $validations = Validator::make($request->all(), $rules);
        if ($validations->fails()) {
            return response()->json([
                "message" => $validations->getMessageBag()->first(),
                'errors' => $validations->errors(),
            ], 422);
        }

        $service->update($request->only(['name', 'client_id', 'is_active']));

        return response()->json([
            'data' => new ServiceListResource($service),
            'message' => 'Service has been updated.'
        ]);
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

    public function validateClient(Request $request)
    {
        $clientId = $request->get('client_id');

        if (!$clientId) {
            throw new RuntimeException('Client ID is required');
        }

        return $this->service->getClientDetails($clientId);
    }
}

<?php

namespace Esanj\AppService\Http\Controllers;

use Esanj\AppService\Http\Resources\ServiceListResource;
use Esanj\AppService\Model\Service;
use Esanj\Manager\Http\Middleware\CheckManagerPermissionMiddleware;
use Illuminate\Http\Request;

class AppServiceApiController extends BaseController
{
    public function __construct()
    {
        $this->middleware(CheckManagerPermissionMiddleware::class .
            ":" . config('app-service.permissions.services.list'))->only('index');
    }

    public function index(Request $request)
    {
        $query = Service::query();


        if ($request->filled('only_trash') && $request->get('only_trash') == 1) {
            $query = $query->whereNotNull('deleted_at')->withTrashed();
        }

        if ($request->filled('serach')) {
            $query = $query->where(function ($query) use ($request) {
                return $query->where('name', 'like', '%' . $request->get('serach') . '%')
                    ->orWhere('client_id', 'like', '%' . $request->get('serach') . '%');
            });
        }

        $query = $query->paginate($request->get('per_page', 10));

        return response()->json(
            ServiceListResource::collection($query)
                ->additional(['totalRecords' => $query->total()])
                ->response()
                ->getData(true)
        );
    }

    public function destroy(Service $service)
    {
        $service->delete();

        return response()->json([]);
    }

    public function restore(int $id)
    {
        Service::withTrashed()->find($id)->restore();

        return response()->json([]);
    }
}

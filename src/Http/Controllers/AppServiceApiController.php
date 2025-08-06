<?php

namespace Esanj\AppService\Http\Controllers;

use Esanj\AppService\Http\Resources\ServiceListResource;
use Esanj\AppService\Model\Service;
use Esanj\AppService\Services\OAuthService;
use Esanj\Manager\Http\Middleware\CheckManagerPermissionMiddleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use RuntimeException;


class AppServiceApiController extends BaseController
{
    public function __construct(protected OAuthService $oAuthService)
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

        if ($request->filled('search')) {
            $query = $query->where(function ($query) use ($request) {
                return $query->where('name', 'like', '%' . $request->get('search') . '%')
                    ->orWhere('client_id', 'like', '%' . $request->get('search') . '%');
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

    public function validateClient(Request $request)
    {
        $clientId = $request->get('client_id');

        if (!$clientId) {
            throw new RuntimeException('Client ID is required');
        }


        $token = $this->oAuthService->getAccessToken();

        if (empty($token['access_token'])) {
            throw new RuntimeException('Access token not found');
        }

        $url = config('app-service.accounting_base_url') . "/api/application/clients/{$clientId}";

        $response = Http::withToken($token['access_token'])->get($url);

        if ($response->failed()) {
            return response()->json($response->json(), $response->status());
        }


        return $response->json();
    }
}

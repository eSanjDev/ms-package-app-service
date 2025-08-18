<?php

namespace Esanj\AppService\Http\Controllers;

use Esanj\AppService\Model\Service;
use Esanj\AppService\Services\OAuthService;
use Esanj\Manager\Http\Middleware\CheckManagerPermissionMiddleware;
use Illuminate\Http\Request;

class AppServiceController extends BaseController
{
    public function __construct(protected OAuthService $oAuthService)
    {
        $this->middleware(CheckManagerPermissionMiddleware::class .
            ":" . config('esanj.app_service.permissions.services.list'))->only('index');
        $this->middleware(CheckManagerPermissionMiddleware::class .
            ":" . config('esanj.app_service.permissions.services.create'))->only(['create', 'store']);
        $this->middleware(CheckManagerPermissionMiddleware::class .
            ":" . config('esanj.app_service.permissions.services.update'))->only(['edit', 'update']);
        $this->middleware(CheckManagerPermissionMiddleware::class .
            ":" . config('esanj.app_service.permissions.services.delete'))->only('destroy');
    }

    public function index()
    {
        return view('app-service::index');
    }

    public function create()
    {
        return view('app-service::create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:services,name'],
            'client_id' => ['required', 'string', 'max:255'],
            'is_active' => ['boolean'],
        ]);

        $service = Service::create([
            'name' => $request->get('name'),
            'client_id' => $request->get('client_id'),
            'is_active' => $request->get("is_active"),
        ]);

        return redirect()->route('services.edit', $service)->with('success', 'Service has been created.');
    }

    public function edit(Service $service)
    {
        return view('app-service::edit', compact('service'));
    }

    public function update(Request $request, Service $service)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:services,name,' . $service->id],
            'client_id' => ['required', 'string', 'max:255'],
            'is_active' => ['boolean'],
        ]);

        $service->update($request->only(['name', 'client_id', 'is_active']));

        return redirect()->route('services.edit', $service)->with('success', 'Service has been updated.');
    }
}

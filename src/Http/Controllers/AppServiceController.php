<?php

namespace Esanj\AppService\Http\Controllers;

use Esanj\Manager\Http\Middleware\CheckManagerPermissionMiddleware;

class AppServiceController extends BaseController
{
    public function __construct()
    {
        $this->middleware(CheckManagerPermissionMiddleware::class . ":" . config('app-service.permissions.app_service.list'));
    }

    public function index()
    {
        return view('app-service::index');
    }
}

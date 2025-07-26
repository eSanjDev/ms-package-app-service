<?php

use Esanj\AppService\Http\Controllers\AppServiceController;
use Esanj\Manager\Http\Middleware\CheckAuthManagerMiddleware;
use Illuminate\Support\Facades\Route;


Route::prefix(config('app-service.routes.web_prefix') . '/app-services')
    ->middleware(['web', CheckAuthManagerMiddleware::class])
    ->group(function () {
        Route::resource('/', AppServiceController::class);
    });

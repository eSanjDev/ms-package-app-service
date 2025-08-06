<?php

use Esanj\AppService\Http\Controllers\AppServiceController;
use Esanj\Manager\Http\Middleware\CheckAuthManagerMiddleware;
use Illuminate\Support\Facades\Route;


Route::prefix(config('app_service.routes.web_prefix'))
    ->middleware(['web', CheckAuthManagerMiddleware::class])
    ->group(function () {
        Route::resource('/services', AppServiceController::class)->except(['destroy', 'show']);
    });

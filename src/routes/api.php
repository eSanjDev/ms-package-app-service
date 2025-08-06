<?php

use Esanj\AppService\Http\Controllers\AppServiceApiController;
use Esanj\Manager\Http\Middleware\CheckAuthManagerMiddleware;
use Illuminate\Support\Facades\Route;

Route::prefix(config('app-service.routes.api_prefix'))
    ->middleware(['api', CheckAuthManagerMiddleware::class])
    ->group(function () {
        Route::apiResource('/services', AppServiceApiController::class)->only(['index', 'destroy']);
        Route::post('/services/{id}/restore', [AppServiceApiController::class, 'restore']);
        Route::get("/services/validation", [AppServiceApiController::class, 'validateClient']);
    });

<?php

use Esanj\AppService\Http\Controllers\AppServiceApiController;
use Illuminate\Support\Facades\Route;

Route::prefix(config('app_service.routes.api_prefix'))
    ->middleware(config('app_service.middlewares.api'))
    ->group(function () {
        Route::apiResource('/services', AppServiceApiController::class);
        Route::post('/services/{id}/restore', [AppServiceApiController::class, 'restore']);
        Route::get("/services/validation", [AppServiceApiController::class, 'validateClient']);
    });

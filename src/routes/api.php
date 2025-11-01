<?php

use Esanj\AppService\Http\Controllers\AppServiceApiController;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Route;

Route::prefix(config('esanj.app_service.routes.api_prefix'))
    ->middleware(array_merge([
        'api',
        EncryptCookies::class,
        AddQueuedCookiesToResponse::class,
        StartSession::class
    ], config('esanj.app_service.middlewares.api')))
    ->group(function () {
        Route::post('/services/{id}/restore', [AppServiceApiController::class, 'restore']);
        Route::get("/services/validation", [AppServiceApiController::class, 'validateClient']);
        Route::apiResource('/services', AppServiceApiController::class);
    });

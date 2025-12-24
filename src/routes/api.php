<?php

declare(strict_types=1);

use Esanj\AppService\Http\Controllers\AppServiceApiController;
use Illuminate\Support\Facades\Route;

Route::prefix(config('esanj.app_service.routes.api_prefix'))
    ->middleware(config('esanj.app_service.middlewares.api'))
    ->group(function () {
        Route::post('services/{id}/restore', [AppServiceApiController::class, 'restore'])->name('api.services.restore');
        Route::get('services/validation', [AppServiceApiController::class, 'validateClient'])->name('api.services.validation');
        Route::apiResource('services', AppServiceApiController::class)->names('api.services');
    });
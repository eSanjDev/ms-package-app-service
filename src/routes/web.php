<?php

declare(strict_types=1);

use Esanj\AppService\Http\Controllers\AppServiceController;
use Illuminate\Support\Facades\Route;

Route::prefix(config('esanj.app_service.routes.web_prefix'))
    ->middleware(config('esanj.app_service.middlewares.web'))
    ->group(function () {
        Route::resource('services', AppServiceController::class)->except(['show']);
        Route::post('services/{id}/restore', [AppServiceController::class, 'restore'])->name('services.restore');
        Route::get('services/validation', [AppServiceController::class, 'validateClient'])->name('services.validation');
    });
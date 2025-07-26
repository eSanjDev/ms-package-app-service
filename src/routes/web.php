<?php

use Esanj\AppService\Http\Controllers\AppServiceController;
use Esanj\Manager\Http\Middleware\CheckAuthManagerMiddleware;
use Illuminate\Support\Facades\Route;


Route::prefix(config('app-service.routes.web_prefix'))
    ->middleware(['web', CheckAuthManagerMiddleware::class])
    ->name('admin.')
    ->group(function () {
        Route::resource('/services', AppServiceController::class)
            ->names('services')
            ->except(['destroy', 'show']);
    });

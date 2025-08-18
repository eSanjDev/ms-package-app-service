<?php

use Esanj\AppService\Http\Controllers\AppServiceController;
use Illuminate\Support\Facades\Route;


Route::prefix(config('esanj.app_service.routes.web_prefix'))
    ->middleware(config('esanj.app_service.middlewares.web'))
    ->group(function () {
        Route::resource('/services', AppServiceController::class)->except(['destroy', 'show']);
    });

<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Simtabi\Laranail\Demo\Mode\Http\Controllers\DemoController;

if ((bool) config('demo-mode.routes.enabled', true)) {
    $throttle = (string) config('demo-mode.reset.on_demand.throttle', '3,1');

    Route::prefix((string) config('demo-mode.routes.prefix', 'demo'))
        ->middleware((array) config('demo-mode.routes.middleware', ['web']))
        ->group(function () use ($throttle): void {
            Route::post('reset', [DemoController::class, 'reset'])
                ->middleware('throttle:'.$throttle)
                ->name('demo-mode.reset');
        });
}

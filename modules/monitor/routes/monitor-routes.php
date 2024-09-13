<?php

use Modules\Monitor\Http\Controllers\HealthCheckController;
use Spatie\Health\Http\Controllers\HealthCheckResultsController;

Route::get('health', HealthCheckResultsController::class);
Route::get('metrics', [HealthCheckController::class, 'metrics']); //->middleware('bearer.token');
Route::get('/', [HealthCheckController::class, 'health']);

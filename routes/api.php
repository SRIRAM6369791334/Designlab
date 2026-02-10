<?php

use App\Http\Controllers\Api\AssetController;
use App\Http\Controllers\Api\CanvasController;
use App\Http\Controllers\Api\DesignController;
use App\Http\Controllers\Api\ExportController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'throttle:api'])->group(function (): void {
    Route::apiResource('designs', DesignController::class);
    Route::get('designs/{design}/canvas', [CanvasController::class, 'load']);

    Route::post('assets', [AssetController::class, 'store'])
        ->middleware('throttle:uploads');

    Route::post('designs/{design}/exports/queue', [ExportController::class, 'queue'])
        ->middleware('throttle:exports');

    Route::post('designs/{design}/exports/instant', [ExportController::class, 'instant'])
        ->middleware('throttle:exports');
});

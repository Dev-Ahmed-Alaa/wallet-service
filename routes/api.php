<?php

use App\Http\Controllers\Api\AuthController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);

    Route::middleware(['auth:sanctum', 'throttle:10,1'])->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
    });
});

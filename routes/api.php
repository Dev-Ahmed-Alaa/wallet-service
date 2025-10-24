<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\WalletController;
use App\Http\Middleware\FinancialSecurityMiddleware;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);

    Route::middleware(['auth:sanctum', 'throttle:10,1'])->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::post('/generate-pin', [AuthController::class, 'generatePin']);

        Route::middleware(FinancialSecurityMiddleware::class)
            ->prefix('wallet')
            ->group(function () {
                Route::post('', [WalletController::class, 'wallet']);
                Route::post('/ledger', [WalletController::class, 'ledger']);

                Route::post('/deposit', [WalletController::class, 'deposit']);
                Route::post('/withdraw', [WalletController::class, 'withdraw']);
                Route::post('/transfer', [WalletController::class, 'transfer']);
            });
    });
});

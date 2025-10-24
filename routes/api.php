<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\WalletController;
use App\Http\Middleware\FinancialSecurityMiddleware;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {

    Route::get('/healthz', function () {
        try {
            DB::connection()->getPdo();
            $dbStatus = 'connected';
        } catch (\Exception $e) {
            $dbStatus = 'error';
        }

        return response()->json([
            'status' => 'ok',
            'app' => config('app.name'),
            'database' => $dbStatus,
            'time' => now()->toDateTimeString(),
        ]);
    });

    Route::middleware('throttle:10,1')->group(function () {
        Route::post('/register', [AuthController::class, 'register']);
        Route::post('/login', [AuthController::class, 'login']);
    });

    Route::middleware(['auth:sanctum', 'throttle:10,1'])->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::post('/generate-pin', [AuthController::class, 'generatePin']);

        Route::prefix('wallet')->group(function () {

            Route::get('/ledger', [WalletController::class, 'ledger']);

            Route::middleware(FinancialSecurityMiddleware::class)->group(function () {
                Route::post('', [WalletController::class, 'wallet']);
                Route::post('/deposit', [WalletController::class, 'deposit']);
                Route::post('/withdraw', [WalletController::class, 'withdraw']);
                Route::post('/transfer', [WalletController::class, 'transfer']);
            });
        });
    });
});

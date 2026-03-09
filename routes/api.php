<?php

use Illuminate\Support\Facades\Route;

Route::post('/login', [\App\Http\Controllers\AuthController::class, 'login']);
Route::post('/checkout', [\App\Http\Controllers\CheckoutController::class, 'store']);

Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('products', \App\Http\Controllers\ProductController::class);
    Route::apiResource('users', \App\Http\Controllers\UserController::class);
    Route::apiResource('clients', \App\Http\Controllers\ClientController::class)->only(['index', 'show']);
    Route::get('gateways', [\App\Http\Controllers\GatewayController::class, 'index']);
    Route::patch('gateways/{id}', [\App\Http\Controllers\GatewayController::class, 'update']);
    Route::get('transactions', [\App\Http\Controllers\TransactionController::class, 'index']);
    Route::get('transactions/{id}', [\App\Http\Controllers\TransactionController::class, 'show']);
    Route::post('transactions/{id}/refund', [\App\Http\Controllers\TransactionController::class, 'refund']);
});

<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\MenuItemController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\RestaurantTableController;
use App\Http\Controllers\TestController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/test', [TestController::class, 'test']);

Route::apiResource('products', ProductController::class);

Route::prefix('api')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);

    Route::middleware('auth:api')->group(function () {
        Route::get('/me', [AuthController::class, 'me']);
        Route::post('/logout', [AuthController::class, 'logout']);

        Route::middleware('role:Client')->group(function () {
            Route::apiResource('menu-items', MenuItemController::class)->only(['index', 'show']);
            Route::apiResource('tables', RestaurantTableController::class)->only(['index', 'show']);
            Route::apiResource('reservations', ReservationController::class)->only(['index', 'show', 'store']);
            Route::apiResource('orders', OrderController::class)->only(['index', 'show', 'store']);
        });

        Route::middleware('role:Manager')->group(function () {
            Route::apiResource('customers', CustomerController::class)->only(['index', 'show', 'store', 'update']);
            Route::apiResource('menu-items', MenuItemController::class)->only(['store', 'update']);
            Route::apiResource('tables', RestaurantTableController::class)->only(['store', 'update']);
            Route::apiResource('reservations', ReservationController::class)->only(['update']);
            Route::apiResource('orders', OrderController::class)->only(['update']);
        });

        Route::middleware('role:Admin')->group(function () {
            Route::delete('customers/{customer}', [CustomerController::class, 'destroy']);
            Route::delete('menu-items/{menu_item}', [MenuItemController::class, 'destroy']);
            Route::delete('tables/{table}', [RestaurantTableController::class, 'destroy']);
            Route::delete('reservations/{reservation}', [ReservationController::class, 'destroy']);
            Route::delete('orders/{order}', [OrderController::class, 'destroy']);
        });
    });
});
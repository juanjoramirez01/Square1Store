<?php

use App\Http\Controllers\Api\v1\AuthController;
use App\Http\Controllers\Api\v1\OrderController;
use App\Http\Controllers\Api\v1\ProductController;
use App\Http\Controllers\Api\v1\CartController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group( function () {
    
    Route::prefix('products')->group( function () {
        Route::get('/search', [ProductController::class, 'search']);
        Route::get('/', [ProductController::class, 'index']);
        Route::get('/{ProductID}', [ProductController::class, 'show']);
        Route::post('/add', [ProductController::class, 'store']);
        Route::put('/update/{ProductID}', [ProductController::class, 'update']);
        Route::delete('/remove/{ProductID}', [ProductController::class, 'destroy']);
    });
    
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::middleware('auth:sanctum')->group(function () {
        
        Route::get('/profile', [AuthController::class, 'profile']);
        Route::post('/logout', [AuthController::class, 'logout']);

        Route::prefix('orders')->group(function () {
            Route::get('/', [OrderController::class, 'index']);
            Route::get('/{OrderID}', [OrderController::class, 'show']);
            Route::post('/create', [OrderController::class, 'store']);
        });
    
        Route::prefix('cart')->group(function () {
            Route::get('/', [CartController::class, 'index']);
            Route::post('/add', [CartController::class, 'add']);
            Route::put('/update/{CartItemID}', [CartController::class, 'update']);
            Route::delete('/remove/{CartItemID}', [CartController::class, 'destroy']);
        });
    });
});
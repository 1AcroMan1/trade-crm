<?php

use Illuminate\Support\Facades\Route;

Route::prefix('api')->group(function () {
    Route::get('warehouses', [\App\Http\Controllers\Api\WarehouseController::class, 'index']);
    Route::get('products', [\App\Http\Controllers\Api\ProductController::class, 'index']);
    Route::get('movement', [\App\Http\Controllers\Api\ProductMovementController::class, 'index']);

    Route::prefix('orders')->group(function () {
        Route::get('/', [\App\Http\Controllers\Api\OrderController::class, 'index']);
        Route::get('/{order}', [\App\Http\Controllers\Api\OrderController::class, 'show']); 
        Route::post('/', [\App\Http\Controllers\Api\OrderController::class, 'store']);
        Route::put('/{order}', [\App\Http\Controllers\Api\OrderController::class, 'update']);
        Route::post('/{order}/complete', [\App\Http\Controllers\Api\OrderController::class, 'complete']);
        Route::post('/{order}/cancel', [\App\Http\Controllers\Api\OrderController::class, 'cancel']);
        Route::post('/{order}/resume', [\App\Http\Controllers\Api\OrderController::class, 'resume']);
    });

    Route::get('stocks', [\App\Http\Controllers\Api\StockController::class, 'index']);
    Route::get('product-movements', [\App\Http\Controllers\Api\ProductMovementController::class, 'index']);
});

Route::get('/test', function () {
    return 'Laravel works!';
});

Route::get('/{any}', function () {
    return view('app');
})->where('any', '.*');

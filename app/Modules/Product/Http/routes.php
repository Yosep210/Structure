<?php

use App\Modules\Product\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Product Module Routes (Web)
|--------------------------------------------------------------------------
|
| Directives and web endpoints for Product module.
|
*/

Route::middleware(['web', 'auth'])->group(function () {
    Route::middleware(['admin'])->group(function () {
        Route::get('/product/stock/', [ProductController::class, 'stockProduct'])->name('product.stock');
        Route::get('/product/sales/', [ProductController::class, 'orderToCompany'])->name('product.sales');
    });

    Route::get('/product/send/{username?}', [ProductController::class, 'sendProduct'])->name('product.send');
    Route::get('/product/usage/{username?}', [ProductController::class, 'usageProduct'])->name('product.usage');
    Route::get('/product/total/{username?}', [ProductController::class, 'totalProduct'])->name('product.total');
    Route::get('/product/member/{username?}', [ProductController::class, 'memberProduct'])->name('product.member');
    Route::get('/product/history/', [ProductController::class, 'historySendProduct'])->name('product.history');
    Route::get('/product/history-ro/', [ProductController::class, 'historyRo'])->name('product.history-ro');
});

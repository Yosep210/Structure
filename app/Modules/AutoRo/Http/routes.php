<?php

use App\Modules\AutoRo\Http\Controllers\AutoRoController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| AutoRo Module Routes (Web)
|--------------------------------------------------------------------------
|
| Directives and web endpoints for AutoRo module.
|
*/

Route::middleware(['web', 'auth'])->group(function () {
    Route::middleware(['admin'])->group(function () {
        Route::get('/autoro', [AutoRoController::class, 'autoRoList'])->name('autoro.list');
    });

    Route::get('/autoro/monthly', [AutoRoController::class, 'autoRoMonthly'])->name('autoro.monthly');
    Route::get('/autoro/history/{username?}', [AutoRoController::class, 'autoRoHistory'])->name('autoro.history');
});

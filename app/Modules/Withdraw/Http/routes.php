<?php

use App\Modules\Withdraw\Http\Controllers\WithdrawController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Withdraw Module Routes (Web)
|--------------------------------------------------------------------------
|
| Directives and web endpoints for Withdraw module.
|
*/

Route::middleware(['web', 'auth'])->group(function () {
    Route::middleware(['admin'])->group(function () {
        Route::get('/withdraw/total', [WithdrawController::class, 'withdrawTotal'])->name('withdraw.total');
        Route::put('/withdraw/confirm/{id}', [WithdrawController::class, 'confirm'])
            ->middleware(['throttle:30,1'])
            ->name('withdraw.confirm');
    });

    Route::get('/withdraw', [WithdrawController::class, 'withdrawList'])->name('withdraw.list');
});

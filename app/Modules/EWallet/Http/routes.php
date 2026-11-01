<?php

use App\Modules\EWallet\Http\Controllers\EWalletController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| EWallet Module Routes (Web)
|--------------------------------------------------------------------------
|
| Directives and web endpoints for EWallet module.
|
*/

Route::middleware(['web', 'auth'])->group(function () {
    Route::middleware(['admin'])->group(function () {});

    Route::get('/wallet/{username?}', [EWalletController::class, 'eWalletList'])->name('wallet.list');
});

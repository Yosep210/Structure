<?php

use App\Modules\Bonus\Http\Controllers\BonusController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Bonus Module Routes (Web)
|--------------------------------------------------------------------------
|
| Directives and web endpoints for Bonus module.
|
*/

Route::middleware(['web', 'auth'])->group(function () {
    Route::middleware(['admin'])->group(function () {
        Route::get('/commission/bonus', [BonusController::class, 'bonusList'])->name('bonus.list');
        Route::get('/commission/statements', [BonusController::class, 'bonusList'])->name('bonus.statements');
    });

    Route::get('/commission/history/{username?}', [BonusController::class, 'bonusHistory'])->name('bonus.history');
    Route::get('/commission/statement/{username?}', [BonusController::class, 'statementMember'])->name('bonus.statement');
});

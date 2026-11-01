<?php

use App\Modules\Auth\Http\Controllers\ImpersonateController;
use App\Modules\Auth\Http\Controllers\NewPasswordController;
use App\Modules\Auth\Http\Controllers\PasswordResetLinkController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Auth Module Routes (Web)
|--------------------------------------------------------------------------
|
| Directives and web endpoints for Auth module.
|
*/

Route::middleware(['web', 'auth'])->group(function () {
    Route::middleware(['admin', 'throttle:30,1'])->group(function () {
        Route::get('/start/{username}', [ImpersonateController::class, 'start'])->name('impersonate.start');
    });

    Route::middleware(['admin'])->group(function () {});

    Route::get('/stop', [ImpersonateController::class, 'stop'])->name('impersonate.stop');
});

Route::middleware(['web', 'guest:web', 'throttle:30,1'])->group(function () {
    Route::post('/forgot-password', [PasswordResetLinkController::class, 'store'])->name('password.username');
    Route::get('/reset-password/{token}', [NewPasswordController::class, 'create'])->name('password.reset');
    Route::post('/reset-password/', [NewPasswordController::class, 'store'])->name('password.update');
});

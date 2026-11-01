<?php

use Illuminate\Support\Facades\Route;

Route::inertia('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'dashboard')->name('dashboard');
});

require __DIR__.'/settings.php';
require base_path('app/Modules/Auth/Http/routes.php');
require base_path('app/Modules/AutoRo/Http/routes.php');
require base_path('app/Modules/Bonus/Http/routes.php');
require base_path('app/Modules/EWallet/Http/routes.php');
require base_path('app/Modules/Member/Http/routes.php');
require base_path('app/Modules/Product/Http/routes.php');
require base_path('app/Modules/Withdraw/Http/routes.php');

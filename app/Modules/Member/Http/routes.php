<?php

use App\Modules\Member\Http\Controllers\MemberController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Member Module Routes (Web)
|--------------------------------------------------------------------------
|
| Directives and web endpoints for Member module.
|
*/

Route::middleware(['web', 'auth'])->group(function () {
    Route::middleware(['admin'])->group(function () {
        Route::get('/member/lists', [MemberController::class, 'memberList'])->name('member.list');
        Route::get('/member/sponsorlists', [MemberController::class, 'sponsorList'])->name('member.sponsor');
        Route::patch('/member/status/{username}', [MemberController::class, 'memberStatus'])
            ->middleware(['throttle:30,1'])
            ->name('member.status');
    });

    Route::get('/member/generation/{username?}', [MemberController::class, 'generationList'])->name('member.generation');
    Route::get('/member/tree/{username?}', [MemberController::class, 'binaryMember'])->name('member.tree');
    Route::get('/register/report', [MemberController::class, 'reportReg'])->name('register.report');
});

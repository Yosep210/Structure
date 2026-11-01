<?php

namespace App\Modules\Auth\Http\Controllers;

use App\Concerns\PasswordValidationRules;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\Auth\PasswordBroker;
use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Laravel\Fortify\Actions\CompletePasswordReset;
use Laravel\Fortify\Contracts\FailedPasswordResetResponse;
use Laravel\Fortify\Contracts\PasswordResetResponse;
use Laravel\Fortify\Contracts\ResetPasswordViewResponse;
use Laravel\Fortify\Contracts\ResetsUserPasswords;

class NewPasswordController extends Controller
{
    use PasswordValidationRules;

    protected $guard;

    public function __construct(Request $request, StatefulGuard $guard)
    {
        parent::__construct($request);
        $this->guard = $guard;
    }

    public function create(): ResetPasswordViewResponse
    {
        return app(ResetPasswordViewResponse::class);
    }

    public function store(): Responsable
    {
        $this->request->validate([
            'token' => ['required'],
            'username' => ['required', 'string', 'max:255'],
            'password' => $this->passwordRules(),
        ]);

        $status = $this->broker()->reset(
            $request = $this->request->only(['username', 'password', 'password_confirmation', 'token']),
            function ($user) use ($request) {
                app(ResetsUserPasswords::class)->reset($user, $request);
                app(CompletePasswordReset::class)($this->guard, $user);
            }
        );

        return $status === Password::PASSWORD_RESET
            ? app(PasswordResetResponse::class, ['status' => $status])
            : app(FailedPasswordResetResponse::class, ['status' => $status]);
    }

    protected function broker(): PasswordBroker
    {
        return Password::broker(config('fortify.passwords'));
    }
}

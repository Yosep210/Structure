<?php

namespace App\Modules\Auth\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Member\Domain\Exceptions\MemberException;
use App\Modules\Member\Domain\Interfaces\MemberGetInterface;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Laravel\Fortify\Contracts\FailedPasswordResetLinkRequestResponse;
use Laravel\Fortify\Contracts\RequestPasswordResetLinkViewResponse;
use Laravel\Fortify\Contracts\SuccessfulPasswordResetLinkRequestResponse;

class PasswordResetLinkController extends Controller
{
    public function create(): RequestPasswordResetLinkViewResponse
    {
        return app(RequestPasswordResetLinkViewResponse::class);
    }

    public function store(MemberGetInterface $memberGet): Responsable|RedirectResponse
    {
        $validated = $this->request->validate([
            'username' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255'],
        ]);

        $username = Str::lower(trim($validated['username']));
        $email = Str::lower(trim($validated['email']));

        $member = $memberGet->findByUsername($username);
        if ($member) {
            if (Str::lower(trim($member->email)) !== $email) {
                throw MemberException::getEmail($email, $username);
            }

            $status = Password::broker('users')->sendResetLink([
                'username' => $member->username,
            ]);

            return $status === Password::RESET_LINK_SENT
                ? app(SuccessfulPasswordResetLinkRequestResponse::class, ['status' => $status])
                : app(FailedPasswordResetLinkRequestResponse::class, ['status' => $status]);
        }

        throw MemberException::getUsername($username);
    }
}

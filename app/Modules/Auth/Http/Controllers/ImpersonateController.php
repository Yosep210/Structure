<?php

namespace App\Modules\Auth\Http\Controllers;

use App\Concerns\HasAuth;
use App\Http\Controllers\Controller;
use App\Modules\Member\Domain\Enums\StatusMember;
use App\Modules\Member\Domain\Exceptions\MemberException;
use App\Modules\Member\Domain\Interfaces\MemberGetInterface;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class ImpersonateController extends Controller
{
    use HasAuth;

    public function start(string $username, MemberGetInterface $get): RedirectResponse
    {
        $auth = $this->getAuthMember();
        if (! $auth['isAdmin']) {
            throw MemberException::noAccess();
        }

        $member = $get->findByUsername($username);
        if ($member === null) {
            throw MemberException::getUsername($username);
        }

        if ($member->id === $auth['member']->id) {
            throw MemberException::impersonateMe();
        }

        if ($member->status === StatusMember::INACTIVE) {
            throw MemberException::memberInactive($member->username);
        }

        session()->put('impersonate_id', $auth['member']->id);
        session()->put('impersonate_back_url', url()->previous() ?: route('dashboard'));

        Auth::login($member);

        return redirect()->route('dashboard');
    }

    /**
     * Stop
     */
    public function stop(): RedirectResponse
    {
        $adminId = session()->pull('impersonate_id');
        $backUrl = session()->pull('impersonate_back_url', route('dashboard'));

        if (! $adminId) {
            return redirect()->route('dashboard');
        }

        if (Auth::loginUsingId($adminId)) {
            return redirect()->to($backUrl);
        }

        Auth::logout();

        return redirect()->route('login');
    }
}

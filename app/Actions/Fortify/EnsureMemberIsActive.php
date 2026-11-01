<?php

namespace App\Http\Middleware;

use App\Concerns\HasAuth;
use App\Modules\Member\Domain\Enums\StatusMember;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;

class EnsureMemberIsActive
{
    use HasAuth;

    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $auth = $this->getAuthMember();

        // 1. Ensure member is authenticated
        if (! $auth['member']) {
            return $next($request);
        }

        // 2. Allow Admin to bypass member status checks
        if ($auth['isAdmin']) {
            return $next($request);
        }

        // 3. Ensure Member status is ACTIVE
        if ($auth['member']->status !== StatusMember::ACTIVE) {
            $message = "Member ({$auth['member']->username}) sedang Nonaktif.";

            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $message,
                ], 403);
            }

            Inertia::flash('toast', [
                'type' => 'error',
                'message' => $message,
            ]);

            return redirect()->route('login');
        }

        return $next($request);
    }
}

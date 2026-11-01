<?php

namespace App\Http\Middleware;

use App\Concerns\HasAuth;
use App\Enums\MemberRole;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    use HasAuth;

    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, ?string $role = null): Response
    {
        $auth = $this->getAuthMember();

        // 1. If role is null, allow both roles
        if ($role === null) {
            return $next($request);
        }

        // 2. Validate Admin role authorization
        if ($role === MemberRole::ADMIN->value && ! $auth['isAdmin']) {
            return $this->redirectUnauthorized($request);
        }

        // 3. Validate Member role authorization
        if ($role === MemberRole::MEMBER->value && $auth['isAdmin']) {
            return $this->redirectUnauthorized($request);
        }

        return $next($request);
    }

    /**
     * Redirect unauthorized requests safely.
     * Prevents loop redirects and handles impersonation fallback.
     */
    protected function redirectUnauthorized(Request $request): Response
    {
        $previous = session()->get('_previous.url');
        $stopUrl = Route::has('impersonate.stop') ? route('impersonate.stop') : null;

        if ($previous && $previous !== $stopUrl && $previous !== $request->fullUrl() && $previous !== url('/')) {
            return redirect()->to($previous);
        }

        return redirect()->route('dashboard');
    }
}

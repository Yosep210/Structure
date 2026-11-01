<?php

namespace App\Modules\Auth\Http\Middleware;

use App\Concerns\HasAuth;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
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

        if (! $auth['isAdmin']) {
            $previous = session()->get('_previous.url');
            $stopUrl = route('impersonate.stop');

            if ($previous && $previous !== $stopUrl && $previous !== $request->fullUrl() && $previous !== url('/')) {
                return redirect()->to($previous);
            }

            return redirect()->route('dashboard');
        }

        return $next($request);
    }
}

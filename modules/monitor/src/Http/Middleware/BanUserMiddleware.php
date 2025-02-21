<?php

namespace Modules\Monitor\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class BanUserMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param Closure(Request): (Response) $next
     */
    public function handle(Request $request, Closure $next)
    {
        $user = getUser();
        if ($user && $user->is_banned) {
            auth()->logout();

            return redirect()->to('/')->with('error', 'your account is banned');
        }

        return $next($request);
    }
}

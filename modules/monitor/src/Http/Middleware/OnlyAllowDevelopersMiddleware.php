<?php

namespace Modules\Monitor\Http\Middleware;

use Closure;
use HttpResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class OnlyAllowDevelopersMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param Closure(Request): (Response) $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $mobile = getUser()?->mobile;
        if ($mobile && in_array($mobile, config('developer.backends'))) {
            return $next($request);
        }
        abort(HttpResponse::HTTP_NOT_FOUND);
    }
}

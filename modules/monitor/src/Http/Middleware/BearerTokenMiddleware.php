<?php

namespace Modules\Monitor\Http\Middleware;

use Closure;
use HttpResponse;
use Illuminate\Http\Request;

class BearerTokenMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $bearerToken = $request->bearerToken();

        if ($bearerToken !== config('metrics.brear_token')) {
            return response()->json(['message' => 'Unauthorized'], HttpResponse::HTTP_UNAUTHORIZED);
        }

        return $next($request);
    }
}

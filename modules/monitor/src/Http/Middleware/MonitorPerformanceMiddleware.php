<?php

namespace Modules\Monitor\Http\Middleware;

use App\Enums\RequestTypeEnum;
use App\Models\RequestPerformanceLog;
use Cache;
use Closure;
use DB;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Lottery;
use Illuminate\Support\Str;
use Psr\SimpleCache\InvalidArgumentException;

class MonitorPerformanceMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure(Request): (Response|RedirectResponse) $next
     * @return Response|RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        return $next($request);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function terminate(Request $request): void
    {
        if (isEnvTesting() || $request->is(["admin/*", "livewire/*", '_debugbar/*'])) {
            return;
        }
        $type = $request->is("api/*") ? RequestTypeEnum::api->value : RequestTypeEnum::web->value;
        $sample_rate = 0.05;
        $exclude = !$request->is(['']);
        $cache_key = 'uri_hit_' . $request->route()?->uri();

        $hit = Cache::get($cache_key);

        if (!$hit) {
            Cache::set($cache_key, true, now()->addDays(13));
            $sample_rate = 1;
        }
        if (($type === RequestTypeEnum::web->value && $exclude)) {
            $sample_rate = 1;
        }

        if (Lottery::odds($sample_rate)->choose()) {
            $responseTimeMs = round(microtime(true) - LARAVEL_START, 3) * 1000;
            $currentDatetime = now()->toDateTimeString();
            $queryDurationMs = (int)DB::totalQueryDuration();

//            dispatch(function () use($request, $responseTimeMs, $currentDatetime){
            RequestPerformanceLog::query()->create([
                'type' => $type,
                'duration' => $responseTimeMs,
                'query_duration' => $queryDurationMs,
                'uri' => $request->route()?->uri() ?? "none",
                'domain' => $request->host(),
                'path' => Str::after($request->fullUrl(), $request->host()),
                'ip' => $request->ip(),
                'user_id' => getUser()?->id,
                'created_at' => $currentDatetime
            ]);
//            })->onQueue('');
        }
    }
}

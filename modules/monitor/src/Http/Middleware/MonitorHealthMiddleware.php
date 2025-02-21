<?php

namespace Modules\Monitor\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Modules\Monitor\Enums\MetricTypeEnum;
use Modules\Monitor\Models\HealthMetricLog;

class MonitorHealthMiddleware
{
    protected static int $type = 2; // set default metric type

    public function handle(Request $request, Closure $next, ...$params)
    {
        self::$type = (int)$params[0];

        return $next($request);
    }

    public function terminate($request, $response): void
    {
        $status = $response->getStatusCode();
        if ($status === \HttpResponse::HTTP_TOO_MANY_REQUESTS) {
            return;
        }
        if (isEnvTesting()) {
            $duration = fake()->numberBetween(100, 1000);
        } else {
            $duration = round(microtime(true) - LARAVEL_START, 3) * 1000;
        }

        $meta = match (self::$type) {
            MetricTypeEnum::login_otp->value, MetricTypeEnum::verify_otp->value => $request->ip(),
            default => null,
        };

        HealthMetricLog::query()->create([
            'created_at' => now()->toDateTimeString(),
            'user_id' => getUser()?->id,
            'type' => self::$type,
            'status_code' => $status,
            'duration' => $duration,
            'meta' => $meta,
        ]);
    }
}

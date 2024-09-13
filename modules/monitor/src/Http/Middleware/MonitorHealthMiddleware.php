<?php

namespace Modules\Monitor\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Modules\Monitor\Enums\MetricTypeEnum;
use Modules\Monitor\Models\HealthMetricLog;

class MonitorHealthMiddleware
{
    protected static int $type = 1;

    public function handle(Request $request, Closure $next, ...$params)
    {
        self::$type = (int)$params[0];

        return $next($request);
    }

    public function terminate($request, $response): void
    {
        if (!config('metrics.enabled')) {
            return;
        }

        if (isEnvTesting()) {
            $duration = fake()->numberBetween(100, 1000);
        } else {
            $duration = round(microtime(true) - LARAVEL_START, 3) * 1000;
        }
        if (in_array(self::$type, [
            MetricTypeEnum::verify_otp->value,
            MetricTypeEnum::login_otp->value,
        ], true)) {
            $meta = $request->mobile;
        }

        HealthMetricLog::query()->create([
            'created_at' => now()->toDateTimeString(),
            'user_id' => getUser()?->id,
            'type' => self::$type,
            'requested' => HealthMetricLog::REQUESTED,
            'tracking_type' => null,
            'status_code' => $response->getStatusCode(),
            'duration' => $duration,
            'terminated' => HealthMetricLog::TERMINATED,
            'meta' => $meta ?? null,
        ]);
    }
}

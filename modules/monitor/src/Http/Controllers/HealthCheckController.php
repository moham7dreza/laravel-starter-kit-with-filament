<?php

namespace Modules\Monitor\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Jalalian;
use Modules\Monitor\Services\HealthCheckService;
use Prometheus\CollectorRegistry;
use Prometheus\RenderTextFormat;
use Prometheus\Storage\InMemory;

class HealthCheckController extends Controller
{
    public function __construct(protected HealthCheckService $healthCheckService)
    {
    }

    public function metrics(Request $request)
    {
        $request->validate([
            'internal' => 'in:0,1',
            'productive' => 'in:0,1',
            'sms' => 'in:0,1',
            'gateways' => 'in:0,1',
        ]);

        $result = cache()->remember('prometheus-metrics', config('metrics.cache-ttl'), function () {
            $registry = app(CollectorRegistry::class, ['storageAdapter' => new InMemory()]);

            if (request('internal') ?? config('metrics.internal-services-enabled')) {
                $this->healthCheckService->registerInternalServiceMetrics($registry);
            }

            if (request('productive') ?? config('metrics.productive-enabled')) {
                $this->healthCheckService->registerProductiveMetrics($registry);
            }

            if (request('sms') ?? config('metrics.sms-enabled')) {
                $this->healthCheckService->registerSmsMetrics($registry);
            }

            return app(RenderTextFormat::class)->render($registry->getMetricFamilySamples());
        });

        return response($result)->header('Content-Type', 'text/plain');
    }

    public function health()
    {
        return response()->json([
            'ServiceName' => 'project Api',
            'ServiceVersion' => 'v1.0',
            'HostName' => \request()?->getHost(),
            'Time' => Jalalian::now()->format('Y/m/d H:i:s'),
            'Message' => 'Powered by project Team',
            'Status' => 'healthy'
        ]);
    }
}

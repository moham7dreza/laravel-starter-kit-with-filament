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
            'request' => 'in:0,1',
            'gateways' => 'in:0,1',
        ]);

        $registry = app(CollectorRegistry::class, ['storageAdapter' => new InMemory()]);

        if (request('internal') ?? config('metrics.internal_services_enabled')) {
            $this->healthCheckService->registerInternalServiceMetrics($registry);
        }

        if (request('productive') ?? config('metrics.productive_enabled')) {
            $this->healthCheckService->registerProductiveMetrics($registry);
        }

        if (request('sms') ?? config('metrics.sms_enabled')) {
//            $this->healthCheckService->registerSmsMetrics($registry);
        }

        $renderer = app(RenderTextFormat::class);

        $result = cache()->remember('prometheus_metrics_result',
            config('metrics.cache-ttl'),
            function () use ($renderer, $registry) {
                return $renderer->render($registry->getMetricFamilySamples());
            });

        return response($result)->header('Content-Type', 'text/plain');
    }

    public function health()
    {
        return response()->json([
            'ServiceName' => 'Toprate Api',
            'ServiceVersion' => 'v1.0',
            'HostName' => \request()?->getHost(),
            'Time' => Jalalian::now()->format('Y/m/d H:i:s'),
            'Message' => 'Powered by Toprate Team',
            'Status' => 'healthy'
        ]);
    }
}

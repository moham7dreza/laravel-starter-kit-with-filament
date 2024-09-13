<?php

namespace Modules\Monitor\Providers;

use Illuminate\Support\ServiceProvider;
use Spatie\Prometheus\Collectors\Horizon;
use Spatie\Prometheus\Facades\Prometheus;

class PrometheusServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        if (request('horizon') ?? config('prometheus.export-horizon-metrics')) {
            $this->registerHorizonCollectors();
        }

    }

    public function registerHorizonCollectors(): self
    {
        Prometheus::registerCollectorClasses([
            Horizon\CurrentMasterSupervisorCollector::class,
            Horizon\CurrentProcessesPerQueueCollector::class,
            Horizon\CurrentWorkloadCollector::class,
            Horizon\FailedJobsPerHourCollector::class,
            Horizon\HorizonStatusCollector::class,
            Horizon\JobsPerMinuteCollector::class,
            Horizon\RecentJobsCollector::class,
        ]);

        return $this;
    }
}

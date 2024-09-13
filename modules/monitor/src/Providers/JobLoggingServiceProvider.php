<?php

namespace Modules\Monitor\Providers;

use Carbon;
use DB;
use Event;
use Exception;
use Illuminate\Queue\Events;
use Illuminate\Support\Lottery;
use Illuminate\Support\ServiceProvider;
use Modules\Monitor\Models\JobPerformanceLog;

class JobLoggingServiceProvider extends ServiceProvider
{
    protected $startTime;
    protected $startMemory;
    protected $queryCount;
    protected $totalQueryTime;

    protected array $excludedJobs = [
        // example : Job::class
    ];

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot(): void
    {
        if (config('logging.performance.job.enabled') || !Lottery::odds(config('logging.performance.job.sampling_rate'))->choose()) {
            return;
        }
        Event::listen(Events\JobProcessing::class, function (Events\JobProcessing $event) {
            try {
                if (in_array($event->job->resolveName(), $this->excludedJobs, true)) {
                    return;
                }
                $this->startTime = microtime(true);
                $this->startMemory = memory_get_usage();
                $this->queryCount = 0;
                $this->totalQueryTime = 0;

                DB::listen(function ($query) {
                    $this->queryCount++;
                    $this->totalQueryTime += $query->time;
                });
            } catch (Exception $e) {
                report($e->getMessage());
            }
        });

        Event::listen(Events\JobProcessed::class, function (Events\JobProcessed $event) {
            try {
                if (in_array($event->job->resolveName(), $this->excludedJobs, true)) {
                    return;
                }
                $endTime = microtime(true);
                $endMemory = memory_get_usage();

                $duration = $endTime - $this->startTime;
                $memoryUsage = $endMemory - $this->startMemory;

                $data = [
                    'job' => $event->job->resolveName(),
                    'connection' => $event->job->getConnectionName(),
                    'queue' => $event->job->getQueue(),
                    'attempts' => $event->job->attempts(),
                    'started_at' => Carbon::createFromTimestamp($this->startTime)->toDateTimeString(),
                    'runtime' => $duration,
                    'memory_usage' => $memoryUsage,
                    'query_count' => $this->queryCount,
                    'query_time' => $this->totalQueryTime
                ];

                JobPerformanceLog::query()->create($data);
            } catch (Exception $e) {
                report($e->getMessage());
            }
        });
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register(): void
    {
        //
    }
}

<?php

namespace Modules\Monitor\Providers;

use Carbon;
use DB;
use Event;
use Exception;
use Illuminate\Console\Events;
use Illuminate\Support\ServiceProvider;
use Modules\Monitor\Models\CommandPerformanceLog;

class CommandLoggingServiceProvider extends ServiceProvider
{
    protected $startTime;
    protected $startMemory;
    protected $queryCount;
    protected $totalQueryTime;

    protected $excludedCommands = [
        'horizon:work',
        'health:custom-check',
        'health:schedule-check-heartbeat',
        'health:queue-check-heartbeat',
        'horizon:snapshot'
    ];

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot(): void
    {
        if (!config('logging.performance.command.enabled')) {
            return;
        }
        Event::listen(Events\CommandStarting::class, function (Events\CommandStarting $event) {
            try {
                if (in_array($event->command, $this->excludedCommands, true)) {
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

        Event::listen(Events\CommandFinished::class, function (Events\CommandFinished $event) {
            try {
                if (in_array($event->command, $this->excludedCommands, true)) {
                    return;
                }
                $endTime = microtime(true);
                $endMemory = memory_get_usage();

                $duration = $endTime - $this->startTime;
                $memoryUsage = $endMemory - $this->startMemory;

                $data = [
                    'command' => $event->command ?? 'unknown',
                    'started_at' => Carbon::createFromTimestamp($this->startTime)->toDateTimeString(),
                    'runtime' => $duration,
                    'memory_usage' => $memoryUsage,
                    'query_count' => $this->queryCount,
                    'query_time' => $this->totalQueryTime
                ];

                CommandPerformanceLog::query()->create($data);
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
    public function register()
    {
        //
    }
}

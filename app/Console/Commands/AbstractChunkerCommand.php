<?php

namespace App\Console\Commands;

use App\Jobs\AbstractChunkerJob;
use Illuminate\Console\Command;

abstract class AbstractChunkerCommand extends Command
{
    public function __construct(
        private AbstractChunkerJob $job,
        private int                $batchSize,
        private bool               $withLogging = false,
    )
    {
        parent::__construct();
    }

    public function setBatchSize($batchSize): static
    {
        $this->batchSize = $batchSize;
        return $this;
    }

    public function setJob($job): static
    {
        $this->job = $job;
        return $this;
    }

    public function handleCommand(): int
    {
        $shouldQueue = $this->option('queue') ?? false;

        $count = $this->job->getQueryCount();

        $bar = null;

        $t0 = microtime(true);

        if (!$shouldQueue) {
            $bar = $this->output->createProgressBar($count);
            $bar->start($count);
        }

        for ($chunk = 0; $chunk <= $count; $chunk += $this->batchSize) {

            if ($this->withLogging) {
                dump(compact('chunk'));
            }

            $this->job = $this->job->setOffset($chunk)->setLimit($this->batchSize)->setLogging($this->withLogging);

            if ($shouldQueue) {
                $this->job::dispatch();
            } else {
                $advance = $this->batchSize;

                dispatch_sync($this->job);

                $bar->advance($advance);
            }
        }

        $duration = round($t0 - microtime(true), 4) * 1000;

        if ($this->withLogging) {
            dump(compact("duration"));
        }

        if (!$shouldQueue) {
            $bar->finish();
        }

        return Command::SUCCESS;
    }

    public function setLogging($logging): static
    {
        $this->withLogging = $logging;
        return $this;
    }
}

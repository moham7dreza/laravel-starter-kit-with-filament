<?php

namespace App\Console\Commands;

use App\DataContracts\JobChunkerDTO;
use App\Jobs\AbstractChunkerJob;
use Illuminate\Console\Command;

abstract class AbstractChunkerCommand extends Command
{
    public function handleCommand(JobChunkerDTO $DTO): int
    {
        /* @var AbstractChunkerJob $job */
        $job = app($DTO->job, ['DTO' => $DTO]);

        $shouldQueue = $this->option('queue') ?? false;

        $count = $job->getQueryCount();

        $bar = null;

        $t0 = microtime(true);

        if (!$shouldQueue) {
            $bar = $this->output->createProgressBar();
            $bar->start($count);
        }

        for ($chunk = 0; $chunk <= $count; $chunk += $DTO->batchSize) {

            if ($DTO->logging) {
                dump(compact('chunk'));
            }

            $job = $job->setOffset($chunk)->setLimit($DTO->batchSize)->setLogging($DTO->logging);

            if ($shouldQueue) {
                $job::dispatch();
            } else {
                $advance = $DTO->batchSize;
                if ($chunk + $advance > $count) {
                    $advance = $count - $chunk;
                }

                $job->handle();

                $bar->advance($advance);
            }
        }

        $duration = round(microtime(true) - $t0, 3);
        if ($duration < 1) {
            $duration *= 1000;
        }

        if ($DTO->logging) {
            dump(compact("duration"));
        }

        if (!$shouldQueue) {
            $bar->finish();
        }

        return Command::SUCCESS;
    }
}

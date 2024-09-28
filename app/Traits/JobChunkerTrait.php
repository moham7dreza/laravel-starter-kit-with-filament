<?php

namespace App\Traits;

use App\DataContracts\JobChunkerDTO;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

trait JobChunkerTrait
{
    public function chunkQueryToJobs(JobChunkerDTO $DTO): int
    {
        $shouldQueue = $DTO->shouldQueue;

        $job = new ($DTO->job)(DTO: $DTO);

        $count = $DTO->query->count();

        if ($DTO->logging) {
            dump(compact('count'));
        }

        $bar = null;

        $stopWatchEvent = $DTO->stopwatch->start(
            name: method_exists($job, 'tags') ? $job->tags()[0] : 'job-chunker',
            category: explode(' ', $DTO->signature, 2)[0],
        );

        if (!$shouldQueue) {
            $bar = $DTO->outputStyle->createProgressBar();
            $bar->start($count);
        }

        $data = $DTO->query->orderBy('id')->pluck('id')->collect();

        $data->chunk($DTO->batchSize)
            ->each(function (Collection $chunk) use ($job, $bar, $DTO, $shouldQueue) {

                $DTO->idsRange = $chunk->toArray();

                if ($shouldQueue) {
                    dispatch($job)->onQueue($DTO->queue);
                } else {
                    $job->handle();
                    $bar->advance($chunk->count());
                }
            });

        $stopWatchStop = $DTO->stopwatch->stop($stopWatchEvent->getName());

        $duration = round($stopWatchStop->getDuration() / 1000, 3);

        info($stopWatchStop);

        if ($DTO->logging) {
            dump(compact("duration"));
        }

        if (!$shouldQueue) {
            $bar->finish();
        }

        return Command::SUCCESS;
    }

    /**
     * Execute the job.
     */
    public function handleChunkedJob(JobChunkerDTO $DTO): void
    {
        $query = $DTO->query;

        $query->getModel()::query()
            ->when($DTO->hydrateResult, function (Builder $builder) use ($query) {
                $builder->hydrate($query->get()->toArray());
            })
            ->whereIn('id', $DTO->idsRange)
            ->each(function (Model $item) use ($DTO) {

                if ($DTO->logging) {
                    $modelId = $item->getKey();

                    dump(compact('modelId'));
                }

                try {
                    ($DTO->itemHandler)($item);
                } catch (Exception $e) {
                    report($e);
                    info('Failed to execute batch query for model ' . ($modelId ?? null));
                }
            });
    }
}

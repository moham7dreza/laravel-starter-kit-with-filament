<?php

namespace App\Jobs;

use Closure;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Queue\Queueable;

abstract class AbstractChunkerJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        private ?int                                                 $offset = null,
        private ?int                                                 $limit = null,
        protected \Illuminate\Database\Eloquent\Builder|Builder|null $query = null,
        private ?bool                                                $withLogging = null,
    )
    {
        //
    }

    public function setLimit($limit): static
    {
        $this->limit = $limit;
        return $this;
    }

    public function setOffset($offset): static
    {
        $this->offset = $offset;
        return $this;
    }

    public function setQuery($query): static
    {
        $this->query = $query;
        return $this;
    }

    public function setLogging($logging): static
    {
        $this->withLogging = $logging;
        return $this;
    }

    public function getQueryCount(): int
    {
        $count = $this->query->count();

        if ($this->withLogging) {
            dump(compact('count'));
        }

        return $count;
    }

    /**
     * Execute the job.
     */
    public function handleJob(Closure $handler): void
    {
        if (isset($this->limit, $this->offset)) {
            $batchQuery = $this->query->offset($this->offset)->limit($this->limit)->get();
        } else {
            $batchQuery = $this->query->get();
        }

        if ($this->withLogging) {
            $batchQueryCount = $batchQuery->count();

            dump(compact('batchQueryCount'));
        }

        $batchQuery->each(function ($item) use ($handler) {

            if ($this->withLogging && $item instanceof Model) {
                $modelId = $item->getKey();

                dump(compact('modelId'));
            }

            try {
                $handler($item);
            } catch (Exception $e) {
                report($e);
                info('Failed to execute batch query for model ' . ($modelId ?? null));
            }
        });
    }
}

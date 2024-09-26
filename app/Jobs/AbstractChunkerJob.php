<?php

namespace App\Jobs;

use Closure;
use DB;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Queue\Queueable;

abstract class AbstractChunkerJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        private ?int                                       $offset = null,
        private ?int                                       $limit = null,
        protected ?string                                  $query = null,
        protected ?array                                   $bindings = null,
        private ?bool                                      $withLogging = null,
        private Builder|null $mainQuery = null,
        private ?string                                    $model = null,
    )
    {
        //
    }

    public function setModel($model): static
    {
        $this->model = $model;
        return $this;
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

    public function setBindings($bindings): static
    {
        $this->bindings = $bindings;
        return $this;
    }

    public function setLogging($logging): static
    {
        $this->withLogging = $logging;
        return $this;
    }

    public function getQueryCount(): int
    {
        $count = $this->mainQuery->count();

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
            $batchQuery = $this->mainQuery->offset($this->offset)->limit($this->limit)->get();
        } else {
            $batchQuery = $this->mainQuery->get();
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

    public function prepareMainQuery(): static
    {
        // Rebuild the query as a Builder instance from raw SQL and bindings
        $query = DB::table(DB::raw("({$this->query}) as subquery"))
            ->setBindings($this->bindings);

        // Automatically get the table name from the User model
        $tableName = (new $this->model())->getTable();

        // Use fromSub() to rebuild the Eloquent Builder dynamically
        $this->mainQuery = $this->model::fromSub($query, $tableName);

        return $this;
    }
}

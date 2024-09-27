<?php

namespace App\Console\Commands;

use App\DataContracts\JobChunkerDTO;
use App\Enums\QueueEnum;
use App\Jobs\TestJob;
use App\Models\User;

class TestCommand extends AbstractChunkerCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:test-command {--queue}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $query = User::query()->whereNotNull('email');

        $DTO = app(JobChunkerDTO::class)
            ->setSql($query->toSql())
            ->setBindings($query->getBindings())
            ->setJob(TestJob::class)
            ->setQueue(QueueEnum::default->value)
            ->setModel(User::class)
            ->setBatchSize(100)
            ->setLogging(false)
            ->setShouldQueue($this->option('queue') ?? false);

        return $this->chunkQueryToJobs($DTO);
    }
}

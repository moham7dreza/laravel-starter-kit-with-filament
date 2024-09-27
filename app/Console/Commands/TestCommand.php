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

        $DTO = app(JobChunkerDTO::class);
        $DTO->sql = $query->toSql();
        $DTO->bindings = $query->getBindings();
        $DTO->job = TestJob::class;
        $DTO->queue = QueueEnum::default->value;
        $DTO->model = User::class;
        $DTO->batchSize = 100;
        $DTO->logging = false;

        return $this->handleCommand($DTO);
    }
}
